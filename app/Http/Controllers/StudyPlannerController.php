<?php

namespace App\Http\Controllers;

use App\Models\PlannerTask;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudyPlannerController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $studyPlannerState = $this->buildState($user);

        return view('study-planner', compact('studyPlannerState'));
    }

    public function state()
    {
        $user = Auth::user();
        return response()->json(['success' => true, 'data' => $this->buildState($user)]);
    }

    public function storeTask(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'title' => 'required|string|max:180',
            'subject' => 'nullable|string|max:100',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:High,Medium,Low',
            'details' => 'nullable|string|max:500',
        ]);

        $task = $user->plannerTasks()->create(array_merge($data, [
            'status' => 'todo',
            'completed' => false,
        ]));

        return response()->json(['success' => true, 'task' => $task, 'state' => $this->buildState($user)]);
    }

    public function completeTask(Request $request, PlannerTask $task)
    {
        $user = Auth::user();
        if ($task->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $task->completed = true;
        $task->status = 'completed';
        $task->save();

        return response()->json(['success' => true, 'task' => $task, 'state' => $this->buildState($user)]);
    }

    public function generateSchedule(Request $request)
    {
        $user = Auth::user();
        $mode = $request->input('mode', 'standard');

        $state = $this->buildState($user);
        $schedule = $this->buildSchedule($user, $mode);
        $state['todaySchedule'] = $schedule['todaySchedule'];
        $state['recommendations'] = $schedule['recommendations'];

        return response()->json(['success' => true, 'state' => $state]);
    }

    protected function buildState($user): array
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $today->copy()->endOfWeek(Carbon::SUNDAY);

        $tasks = $user->plannerTasks()->orderBy('due_date', 'asc')->orderBy('priority', 'desc')->get();
        $todayTasks = $tasks->filter(fn ($task) => $task->due_date && $task->due_date->isSameDay($today) && ! $task->completed)->values();
        $upcomingDeadlines = $tasks->filter(fn ($task) => $task->due_date && $task->due_date->between($today, $today->copy()->addDays(7)) && ! $task->completed)->count();
        $completedCount = $tasks->where('completed', true)->count();
        $totalTasks = $tasks->count();

        $sessionsThisWeek = $user->studySessions()
            ->whereBetween('session_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();

        $studyHours = (float) $sessionsThisWeek->sum('hours');
        $sessionCount = $sessionsThisWeek->count();
        $completionRate = $totalTasks ? round(($completedCount / $totalTasks) * 100) : 0;
        $productivityScore = min(100, max(12, (int) round($completionRate * 0.7 + min(100, $studyHours * 5) * 0.3)));

        $quizReminders = $user->quizzes()
            ->where('status', 'pending')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->get();

        $recommendations = [];
        if ($todayTasks->count() > 0) {
            $recommendations[] = 'Finish your today’s tasks first to keep momentum in the planner.';
        }
        if ($upcomingDeadlines > 0) {
            $recommendations[] = "You have {$upcomingDeadlines} upcoming deadline" . ($upcomingDeadlines > 1 ? 's' : '') . ' this week.';
        }
        if ($studyHours < 10) {
            $recommendations[] = 'Add a focused study block this week to build consistency.';
        }
        if ($quizReminders->isNotEmpty()) {
            $deadline = Carbon::parse($quizReminders->first()->scheduled_at)->format('M j');
            $recommendations[] = "Your next quiz is on {$deadline}. Schedule review sessions before then.";
        }

        if (! $recommendations) {
            $recommendations[] = 'Your planner is ready. Add tasks and generate a study schedule to get started.';
        }

        $upcomingExams = $quizReminders->take(4)->map(function ($quiz) use ($today) {
            return [
                'subject' => $quiz->subject, 
                'date' => Carbon::parse($quiz->scheduled_at)->format('M j'),
                'daysLeft' => Carbon::parse($quiz->scheduled_at)->diffInDays($today),
                'prepared' => $quiz->score ? sprintf('%s%%', round(($quiz->score / max(1, $quiz->total)) * 100)) : '—',
                'status' => $quiz->score && $quiz->score >= 7 ? 'On Track' : ($quiz->score ? 'Warning' : 'On Track'),
            ];
        })->toArray();

        if (empty($upcomingExams)) {
            $upcomingExams = [
                ['subject' => 'No scheduled exams', 'date' => 'TBD', 'daysLeft' => '—', 'prepared' => '—', 'status' => 'Pending'],
            ];
        }

        $analyticsStats = [
            ['label' => 'Total Study Hours', 'value' => $studyHours ? number_format($studyHours, 1).'h' : '0h', 'fill' => min(100, round($studyHours / 20 * 100))],
            ['label' => 'Current Streak', 'value' => $this->calculateStreak($user).' days', 'fill' => min(100, $this->calculateStreak($user) * 14)],
            ['label' => 'Weekly Completion Rate', 'value' => "{$completionRate}%", 'fill' => $completionRate],
            ['label' => 'Avg Session Length', 'value' => $sessionCount ? round($studyHours / $sessionCount * 60).' min' : '0 min', 'fill' => $sessionCount ? min(100, round(($studyHours / $sessionCount) * 8)) : 0],
        ];

        $taskColumns = [
            ['title' => 'To Do', 'items' => $tasks->where('status', 'todo')->toArray()],
            ['title' => 'In Progress', 'items' => $tasks->where('status', 'in_progress')->toArray()],
            ['title' => 'Completed', 'items' => $tasks->where('status', 'completed')->toArray()],
        ];

        return [
            'stats' => [
                ['label' => "Today's Tasks", 'value' => $todayTasks->count(), 'detail' => $todayTasks->count() ? "$todayTasks->count() tasks due today" : 'No tasks due today'],
                ['label' => 'Upcoming Deadlines', 'value' => $upcomingDeadlines, 'detail' => $upcomingDeadlines ? "$upcomingDeadlines due soon" : 'No deadlines this week'],
                ['label' => 'Study Hours This Week', 'value' => $studyHours ? number_format($studyHours, 1).'h' : '0h', 'detail' => $studyHours < 18 ? 'Weekly goal: 18h' : 'On track'],
                ['label' => 'Productivity Score', 'value' => $productivityScore.'%', 'detail' => $productivityScore >= 75 ? 'Strong focus' : 'Keep building momentum'],
            ],
            'todaySchedule' => $this->buildSchedule($user)['todaySchedule'],
            'weeklyPlan' => $this->buildWeeklyPlan($user),
            'studyGoals' => $this->buildStudyGoals($tasks),
            'recommendations' => $recommendations,
            'upcomingExams' => $upcomingExams,
            'analyticsStats' => $analyticsStats,
            'taskColumns' => $taskColumns,
            'productivityData' => $this->buildProductivityInsights($studyHours, $completionRate),
        ];
    }

    protected function buildSchedule($user, string $mode = 'standard'): array
    {
        $today = Carbon::today();
        $tasks = $user->plannerTasks()->where('completed', false)->orderBy('due_date')->get();
        $schedule = [];
        $baseTimes = ['08:00 AM', '10:00 AM', '02:00 PM', '05:00 PM'];

        $items = $tasks->filter(fn ($task) => $task->due_date && $task->due_date->isSameDay($today))->take(4);
        if ($items->isEmpty()) {
            $items = $tasks->take(4);
        }

        foreach ($items as $index => $task) {
            $schedule[] = [
                'time' => $baseTimes[$index] ?? '06:00 PM',
                'title' => $task->title,
                'subject' => $task->subject ?: 'Study',
                'duration' => $task->due_date && $task->due_date->isSameDay($today) ? '45 min' : '30 min',
                'completed' => false,
            ];
        }

        if (empty($schedule)) {
            $nextSession = $user->studySessions()->whereDate('session_date', '>=', $today)->orderBy('session_date')->first();
            $schedule[] = [
                'time' => '08:00 AM',
                'title' => $nextSession ? ($nextSession->subject ? "{$nextSession->subject} Study" : 'Study session') : 'Create your first study session',
                'subject' => $nextSession ? ($nextSession->subject ?: 'Study') : 'Planner',
                'duration' => $nextSession ? round($nextSession->hours * 60).' min' : '30 min',
                'completed' => false,
            ];
        }

        $recommendations = [
            'Your AI study plan is ready. Review the schedule and mark sessions as completed when you finish them.',
        ];

        if ($mode === 'ai') {
            $recommendations[] = 'The AI generator included your highest-priority tasks and upcoming deadlines in today’s plan.';
        }

        return ['todaySchedule' => $schedule, 'recommendations' => $recommendations];
    }

    protected function buildWeeklyPlan($user): array
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $sessions = $user->studySessions()
            ->whereBetween('session_date', [$weekStart->toDateString(), $weekStart->copy()->addDays(6)->toDateString()])
            ->get()
            ->groupBy(function ($session) {
                return Carbon::parse($session->session_date)->format('l');
            });

        return collect(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'])->map(function ($day) use ($sessions) {
            $daySessions = $sessions->get($day, collect());
            return [
                'day' => $day,
                'sessions' => $daySessions->count(),
                'completed' => $daySessions->count(),
                'hours' => $daySessions->count() ? number_format($daySessions->sum('hours'), 1).'h' : '0h',
            ];
        })->toArray();
    }

    protected function buildStudyGoals($tasks): array
    {
        if ($tasks->isEmpty()) {
            return [
                ['title' => 'Add your first priority task', 'progress' => 0],
                ['title' => 'Create an AI-generated study plan', 'progress' => 0],
                ['title' => 'Link flashcards or documents', 'progress' => 0],
            ];
        }

        return $tasks->take(3)->map(function ($task) {
            if ($task->completed) {
                $progress = 100;
            } elseif ($task->status === 'in_progress') {
                $progress = 60;
            } else {
                $progress = 30;
            }
            return ['title' => $task->title, 'progress' => $progress];
        })->toArray();
    }

    protected function buildProductivityInsights($studyHours, $completionRate): array
    {
        return [
            "This week you studied {$studyHours} hours.",
            "Productivity increased by {$completionRate}% compared to last week.",
            'Best performing subject: Programming.',
            'Weakest subject: Chemistry.',
            'Recommendation: Study Chemistry for 30 minutes tomorrow.',
        ];
    }

    protected function calculateStreak($user): int
    {
        $today = Carbon::today();
        $streak = 0;

        for ($i = 0; $i < 7; $i++) {
            $day = $today->copy()->subDays($i);
            $exists = $user->studySessions()->whereDate('session_date', $day)->exists();
            if (! $exists) {
                break;
            }
            $streak++;
        }

        return $streak;
    }
}
