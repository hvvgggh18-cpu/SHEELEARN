<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\ActivityLog;
use App\Models\ChatMessage;
use App\Models\Document;
use App\Models\FlashcardDeck;
use App\Models\PlannerTask;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'email_verified_at',
        'password',
        'plan',
        'settings',
        'phone_number',
        'firebase_uid',
        'login_provider',
        'provider_name',
        'provider_id',
        'provider_avatar',
        'phone_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'password' => 'hashed',
        'settings' => 'array',
    ];

    /**
     * Get the user's plan identifier.
     */
    public function getPlanAttribute(): string
    {
        return strtolower($this->attributes['plan'] ?? 'free');
    }

    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class);
    }

    public function decks(): HasMany
    {
        return $this->hasMany(FlashcardDeck::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function studySessions(): HasMany
    {
        return $this->hasMany(StudySession::class);
    }

    public function plannerTasks(): HasMany
    {
        return $this->hasMany(PlannerTask::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function chatConversations(): HasMany
    {
        return $this->hasMany(ChatConversation::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function logActivity(string $action, ?string $subject = null, ?string $details = null, ?string $type = null)
    {
        return $this->activityLogs()->create([
            'action' => $action,
            'subject' => $subject,
            'details' => $details,
            'type' => $type,
        ]);
    }

    /**
     * Get dashboard statistics for this user.
     *
     * @return array<string, mixed>
     */
    public function getDashboardStatsAttribute(): array
    {
        $now = Carbon::now();
        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $studyTime = (float) $this->studySessions()
            ->whereBetween('session_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->sum('hours');

        $previousWeekStart = $weekStart->copy()->subWeek();
        $previousWeekEnd = $weekEnd->copy()->subWeek();

        $previousStudyTime = (float) $this->studySessions()
            ->whereBetween('session_date', [$previousWeekStart->toDateString(), $previousWeekEnd->toDateString()])
            ->sum('hours');

        $studyGoal = 20;
        $goalCompletion = $studyGoal > 0 ? (int) min(100, round(($studyTime / $studyGoal) * 100)) : 0;
        $studyChange = $previousStudyTime > 0
            ? (int) round((($studyTime - $previousStudyTime) / $previousStudyTime) * 100)
            : ($studyTime > 0 ? 100 : 0);

        $flashcardsMastered = $this->flashcards()->where('mastered', true)->count();
        $previousFlashcardsMastered = $this->flashcards()
            ->where('mastered', true)
            ->whereBetween('updated_at', [$previousWeekStart->toDateTimeString(), $previousWeekEnd->toDateTimeString()])
            ->count();

        $flashcardsGoal = 400;
        $flashcardsCompletion = $flashcardsGoal > 0
            ? (int) min(100, round(($flashcardsMastered / $flashcardsGoal) * 100))
            : 0;

        $flashcardsChange = $previousFlashcardsMastered > 0
            ? (int) round((($flashcardsMastered - $previousFlashcardsMastered) / $previousFlashcardsMastered) * 100)
            : ($flashcardsMastered > 0 ? 100 : 0);

        $completedQuizzes = $this->quizzes()
            ->where('status', 'completed')
            ->whereNotNull('score')
            ->whereNotNull('total')
            ->get(['score', 'total', 'completed_at']);

        $quizAccuracy = $completedQuizzes->isNotEmpty()
            ? (int) round($completedQuizzes->avg(function ($quiz) {
                return $quiz->total > 0 ? ($quiz->score / $quiz->total) * 100 : 0;
            }))
            : 0;

        $quizAccuracyChange = 0;
        if ($completedQuizzes->count() >= 2) {
            $sortedQuizzes = $completedQuizzes->sortByDesc('completed_at')->values();
            $half = max(1, (int) floor($sortedQuizzes->count() / 2));
            $latestAvg = $sortedQuizzes->slice(0, $half)->avg(function ($quiz) {
                return $quiz->total > 0 ? ($quiz->score / $quiz->total) * 100 : 0;
            });
            $previousAvg = $sortedQuizzes->slice($half)->avg(function ($quiz) {
                return $quiz->total > 0 ? ($quiz->score / $quiz->total) * 100 : 0;
            });
            $quizAccuracyChange = (int) round($latestAvg - $previousAvg);
        }

        $learningStreak = $this->calculateLearningStreak($now);

        $reviewDue = $this->flashcards()
            ->where('mastered', false)
            ->whereNotNull('due_at')
            ->where('due_at', '<=', $now)
            ->count();

        $reviewTopic = $this->flashcards()
            ->where('mastered', false)
            ->whereNotNull('due_at')
            ->where('due_at', '<=', $now)
            ->orderBy('due_at')
            ->value('subject') ?? 'biology';

        $cardsCompleted = $this->flashcards()->whereNotNull('reviewed_at')->count();
        $quizzesCompleted = $completedQuizzes->count();
        // Module-specific counts
        $aiConversations = $this->chatConversations()->count();
        $chatMessages = $this->chatMessages()->count();
        $documentsCount = \App\Models\Document::where('user_id', $this->id)->count();
        $summariesCount = $this->activityLogs()->where('type', 'summary')->count() + \App\Models\Document::where('user_id', $this->id)->whereNotNull('summary')->count();
        $notesCount = $this->activityLogs()->where('type', 'notes')->count();
        $plannerTasksCount = $this->plannerTasks()->count();
        $tasksRemaining = count(array_filter($this->dashboard_tasks, fn ($task) => !$task['done']));
        $learningActivity = $this->dashboard_learning_activity;
        $peak = collect($learningActivity)->sortByDesc('hours')->first();

        return [
            'study_time_this_week' => $studyTime,
            'study_goal_hours' => $studyGoal,
            'goal_completion' => $goalCompletion,
            'study_completion' => $goalCompletion,
            'study_change' => $studyChange,
            'flashcards_mastered' => $flashcardsMastered,
            'flashcards_change' => $flashcardsChange,
            'flashcards_goal' => $flashcardsGoal,
            'flashcards_completion' => $flashcardsCompletion,
            'quiz_accuracy' => $quizAccuracy,
            'quiz_accuracy_change' => $quizAccuracyChange,
            'learning_streak' => $learningStreak,
            'learning_status' => ($learningStreak > 6 ? 'Active' : 'On Track'),
            'learning_streak_completion' => (int) min(100, round($learningStreak / 30 * 100)),
            'review_due' => $reviewDue,
            'review_topic' => $reviewTopic,
            'next_set_cards' => min(12, max(1, $reviewDue)),
            'cards_completed' => $cardsCompleted,
            'cards_goal' => $flashcardsGoal,
            'quizzes_completed' => $quizzesCompleted,
            'quizzes_goal' => max(4, $quizzesCompleted),
            'tasks_remaining' => $tasksRemaining,
            'peak_day' => $peak['day'] ?? 'Saturday',
            'peak_hours' => $peak['hours'] ?? 0,
            // Module stats
            'ai_conversations' => $aiConversations,
            'chat_messages' => $chatMessages,
            'documents_count' => $documentsCount,
            'summaries_count' => $summariesCount,
            'notes_count' => $notesCount,
            'planner_tasks_count' => $plannerTasksCount,
        ];
    }

    /**
     * Calculate the current learning streak based on study sessions.
     */
    protected function calculateLearningStreak(Carbon $now): int
    {
        $streak = 0;
        $date = $now->copy()->startOfDay();
        $activityTypes = ['study', 'flashcard', 'quiz', 'summary', 'chat', 'document', 'notes'];

        while ($streak < 30) {
            $hasSession = $this->studySessions()
                ->whereDate('session_date', $date->toDateString())
                ->where('hours', '>', 0)
                ->exists();

            $hasActivity = $this->activityLogs()
                ->whereDate('created_at', $date->toDateString())
                ->where(function ($query) use ($activityTypes) {
                    foreach ($activityTypes as $type) {
                        $query->orWhere('type', $type);
                    }
                })
                ->exists();

            if ($hasSession || $hasActivity) {
                $streak++;
                $date->subDay();
                continue;
            }

            break;
        }

        return $streak;
    }

    /**
     * Get weekly learning activity for the dashboard chart.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getDashboardLearningActivityAttribute(): array
    {
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $hoursByDay = $this->studySessions()
            ->whereBetween('session_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get(['session_date', 'hours'])
            ->groupBy(function ($session) {
                return Carbon::parse($session->session_date)->format('D');
            })
            ->map(fn ($sessions) => $sessions->sum('hours'));

        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        return array_map(function (string $day) use ($hoursByDay) {
            return [
                'day' => $day,
                'hours' => round(min(6, $hoursByDay->get($day, 0)), 1),
            ];
        }, $days);
    }

    /**
     * Get the SVG polyline points string for the dashboard learning activity chart.
     */
    public function getDashboardLearningActivityPolylineAttribute(): string
    {
        $points = [];

        foreach ($this->dashboard_learning_activity as $index => $activity) {
            $x = $index * 86;
            $y = 180 - ($activity['hours'] / 6) * 120;
            $points[] = sprintf('%d,%d', $x, $y);
        }

        return implode(' ', $points);
    }

    /**
     * Get the SVG fill path string for the dashboard learning activity chart.
     */
    public function getDashboardLearningActivityPathAttribute(): string
    {
        $points = $this->dashboard_learning_activity_polyline;
        $lastX = (count($this->dashboard_learning_activity) - 1) * 86;

        return sprintf('M0,180 L%s L%d,180 Z', $points, $lastX);
    }

    /**
     * Get dashboard subject progress.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getDashboardSubjectsAttribute(): array
    {
        $subjectHours = $this->studySessions()
            ->select('subject')
            ->selectRaw('SUM(hours) as hours')
            ->groupBy('subject')
            ->orderByDesc('hours')
            ->limit(5)
            ->get();

        if ($subjectHours->isEmpty()) {
            return [];
        }

        $colorMap = [
            'Biology' => 'cy',
            'Chemistry' => 'ac',
            'Mathematics' => 'gn',
            'Physics' => 'rose',
            'English Literature' => 'amber',
        ];

        $gradientMap = [
            'Biology' => 'linear-gradient(90deg,#22d3ee,#0891b2)',
            'Chemistry' => 'linear-gradient(90deg,#818cf8,#6366f1)',
            'Mathematics' => 'linear-gradient(90deg,#34d399,#10b981)',
            'Physics' => 'linear-gradient(90deg,#fb7185,#e11d48)',
            'English Literature' => 'linear-gradient(90deg,#fbbf24,#f59e0b)',
        ];

        return $subjectHours->map(function ($subject) use ($colorMap, $gradientMap) {
            return [
                'name' => $subject->subject,
                'progress' => (int) min(100, round($subject->hours / 10 * 100)),
                'color' => $colorMap[$subject->subject] ?? 'c',
                'gradient' => $gradientMap[$subject->subject] ?? 'linear-gradient(90deg,#22d3ee,#0891b2)',
            ];
        })->toArray();
    }

    /**
     * Get upcoming dashboard tasks.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getDashboardTasksAttribute(): array
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $today->copy()->endOfWeek(Carbon::SUNDAY);

        $reviewDue = $this->flashcards()
            ->where('mastered', false)
            ->whereNotNull('due_at')
            ->where('due_at', '<=', $today)
            ->count();

        $nextQuiz = $this->quizzes()
            ->where('status', 'pending')
            ->whereNotNull('scheduled_at')
            ->orderBy('scheduled_at')
            ->first();

        $studyTime = (float) $this->studySessions()
            ->whereBetween('session_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->sum('hours');

        $tasks = [];

        if ($reviewDue > 0) {
            $tasks[] = [
                'title' => "Review {$reviewDue} due flashcards",
                'duration' => '15 min',
                'status' => 'Today',
                'type' => 'due',
                'done' => false,
            ];
        }

        if ($nextQuiz) {
            $tasks[] = [
                'title' => "Complete {$nextQuiz->subject} quiz",
                'duration' => '30 min',
                'status' => Carbon::parse($nextQuiz->scheduled_at)->format('D'),
                'type' => 'upcoming',
                'done' => false,
            ];
        }

        $upcomingSessions = $this->studySessions()
            ->whereDate('session_date', '>=', $today)
            ->orderBy('session_date')
            ->limit(2)
            ->get();

        foreach ($upcomingSessions as $session) {
            $date = Carbon::parse($session->session_date);
            $tasks[] = [
                'title' => trim("{$session->subject} study session"),
                'duration' => sprintf('%s min', (int) round($session->hours * 60)),
                'status' => $date->isSameDay($today) ? 'Today' : $date->format('D'),
                'type' => 'upcoming',
                'done' => false,
            ];
        }

        // Only return actual user-sourced tasks; do not include generic placeholder items.
        return array_values($tasks);
    }

    /**
     * Get today's dashboard schedule.
     *
     * @return array<int, array<string, string>>
     */
    public function getDashboardScheduleAttribute(): array
    {
        $today = Carbon::today();

        $scheduleItems = $this->studySessions()
            ->whereDate('session_date', '>=', $today)
            ->orderBy('session_date')
            ->limit(3)
            ->get()
            ->map(function ($session) {
                $date = Carbon::parse($session->session_date);

                return [
                    'time' => $date->format('h:i'),
                    'ampm' => $date->format('A'),
                    'title' => "{$session->subject} Study",
                    'subtitle' => sprintf('Study session · %s min', (int) round($session->hours * 60)),
                    'icon' => 'fa-layer-group',
                    'color' => 'cy',
                ];
            })
            ->toArray();

        if (count($scheduleItems) < 3) {
            $nextQuizzes = $this->quizzes()
                ->where('status', 'pending')
                ->whereNotNull('scheduled_at')
                ->whereDate('scheduled_at', '>=', $today)
                ->orderBy('scheduled_at')
                ->limit(3 - count($scheduleItems))
                ->get()
                ->map(function ($quiz) {
                    $date = Carbon::parse($quiz->scheduled_at);

                    return [
                        'time' => $date->format('h:i'),
                        'ampm' => $date->format('A'),
                        'title' => "{$quiz->subject} Quiz",
                        'subtitle' => sprintf('Scheduled quiz · %s', $date->format('M j')),
                        'icon' => 'fa-clipboard-question',
                        'color' => 'ac',
                    ];
                })
                ->toArray();

            $scheduleItems = array_merge($scheduleItems, $nextQuizzes);
        }

        if (empty($scheduleItems)) {
            return [];
        }

        return $scheduleItems;
    }

    /**
     * Get recent dashboard activity.
     *
     * @return array<int, array<string, string>>
     */
    public function getDashboardActivityAttribute(): array
    {
        $logs = $this->activityLogs()->latest()->limit(5)->get();

        if ($logs->isNotEmpty()) {
            return $logs->map(function ($log) {
                $icon = 'fa-circle';
                $color = 'c';

                if (str_contains(strtolower($log->action), 'quiz')) {
                    $icon = 'fa-check';
                    $color = 'gn';
                } elseif (str_contains(strtolower($log->action), 'flashcard')) {
                    $icon = 'fa-layer-group';
                    $color = 'ac';
                } elseif (str_contains(strtolower($log->action), 'upload') || str_contains(strtolower($log->action), 'pdf')) {
                    $icon = 'fa-file-pdf';
                    $color = 'rose';
                } elseif (str_contains(strtolower($log->action), 'summary') || str_contains(strtolower($log->action), 'chat')) {
                    $icon = 'fa-robot';
                    $color = 'amber';
                }

                return [
                    'action' => $log->action,
                    'subject' => $log->subject ?? 'General',
                    'details' => $log->details ?? '',
                    'time' => Carbon::parse($log->created_at)->diffForHumans(),
                    'icon' => $icon,
                    'color' => $color,
                ];
            })->toArray();
        }

        return [];
    }
}
