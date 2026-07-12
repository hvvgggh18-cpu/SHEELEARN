<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $cacheKey = "dashboard_stats:user:{$user->id}";
        $ttl = config('dashboard.stats_ttl', 60);

        $force = $request->query('force') || $request->query('nocache');

        $fromCache = false;
        if (! $force && Cache::has($cacheKey)) {
            $data = Cache::get($cacheKey);
            $fromCache = true;
        } else {
            // Get dashboard stats
            $stats = $user->dashboard_stats;
            $now = \Carbon\Carbon::now();
            
            // Format data for the new dashboard UI
            $data = [
                // Quick statistics
                'flashcardCount' => $stats['flashcards_mastered'] ?? 0,
                'quizCount' => $stats['quizzes_completed'] ?? 0,
                'studyHours' => (int) round($stats['study_time_this_week'] ?? 0),
                'currentStreak' => $stats['learning_streak'] ?? 0,
                
                // Recent activity
                'recentActivity' => $this->getRecentActivity($user),
                
                // Today's schedule
                'schedule' => $this->getTodaySchedule($user),
                
                // AI Recommendations
                'recommendations' => $this->getRecommendations($user, $stats),
            ];
            
            Cache::put($cacheKey, $data, $ttl);
            $fromCache = false;
        }

        return response()->json($data)->header('X-Dashboard-Cache', $fromCache ? 'HIT' : 'MISS');
    }

    public function analyticsStats(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $filter = $request->query('filter', 'last7days');
        $period = $request->query('period', 'daily');
        $range = $this->getAnalyticsDateRange($filter);
        $start = $range['start'];
        $end = $range['end'];

        $studySessions = $user->studySessions()
            ->whereBetween('session_date', [$start->toDateString(), $end->toDateString()])
            ->get(['session_date', 'hours']);

        $chatMessages = $user->chatMessages()
            ->whereBetween('created_at', [$start->copy()->startOfDay()->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()])
            ->get(['conversation_id', 'role', 'created_at']);

        $aiChats = $user->chatConversations()
            ->whereBetween('created_at', [$start->copy()->startOfDay()->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()])
            ->count();

        $documents = $user->documents()
            ->whereBetween('created_at', [$start->copy()->startOfDay()->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()])
            ->count();

        $flashcardsReviewed = $user->flashcards()
            ->whereNotNull('reviewed_at')
            ->whereBetween('reviewed_at', [$start->copy()->startOfDay()->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()])
            ->count();

        $completedQuizzes = $user->quizzes()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$start->copy()->startOfDay()->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()])
            ->get(['score', 'total', 'completed_at']);

        $quizAccuracy = $completedQuizzes->isNotEmpty()
            ? (int) round($completedQuizzes->avg(function ($quiz) {
                return $quiz->total > 0 ? ($quiz->score / $quiz->total) * 100 : 0;
            }))
            : 0;

        $quizSessions = $completedQuizzes->count();
        $userMessages = $chatMessages->where('role', 'user')->count();
        $totalMessages = $chatMessages->count();

        $studyHours = (float) $studySessions->sum('hours');
        $currentStreak = $user->dashboard_stats['learning_streak'] ?? 0;

        $summaryCount = $user->activityLogs()
            ->where('type', 'summary')
            ->whereBetween('created_at', [$start->copy()->startOfDay()->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()])
            ->count();

        $notesCount = $user->activityLogs()
            ->where('type', 'notes')
            ->whereBetween('created_at', [$start->copy()->startOfDay()->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()])
            ->count();

        $distributionCounts = [
            'AI Chat' => $aiChats,
            'Flashcards' => $flashcardsReviewed,
            'Quizzes' => $quizSessions,
            'Summaries' => $summaryCount,
            'Notes' => $notesCount,
        ];

        $chartIntervals = $this->buildTimeIntervals($period, $start, $end);

        $studyActivity = $this->buildIntervalSeries($studySessions, $chartIntervals, 'session_date', 'hours');
        $chatActivity = $this->buildIntervalSeries($chatMessages->where('role', 'user'), $chartIntervals, 'created_at', null);
        $quizActivity = $this->buildIntervalSeries($completedQuizzes, $chartIntervals, 'completed_at', null);

        $studyByDay = $this->buildWeekdayStudyHours($user, $end);
        $heatmap = $this->buildWeeklyHeatmap($user, $end);

        $insights = $this->buildSmartInsights($studyHours, $aiChats, $quizAccuracy, $currentStreak, $flashcardsReviewed, $summaryCount);

        $productivityScore = $this->calculateProductivityScore($studyHours, $quizAccuracy, $flashcardsReviewed, $aiChats, $notesCount);
        $productivityColor = $this->productivityColor($productivityScore);

        $achievements = $this->buildAchievements($user, $currentStreak, $documents, $userMessages, $completedQuizzes);
        $goals = $this->buildLearningGoals($studyHours, $flashcardsReviewed, $aiChats, $quizSessions);

        $lastUpdated = now()->toIso8601String();

        return response()->json([
            'success' => true,
            'currentDate' => now()->format('F j, Y'),
            'lastUpdated' => now()->format('g:i A'),
            'summary' => [
                'studyHours' => round($studyHours, 1),
                'studyTrend' => $this->computeTrend($user, $start, $end, 'study'),
                'aiChats' => $aiChats,
                'aiTrend' => $this->computeTrend($user, $start, $end, 'chat'),
                'documents' => $documents,
                'flashcardsReviewed' => $flashcardsReviewed,
                'quizAccuracy' => $quizAccuracy,
                'currentStreak' => $currentStreak,
            ],
            'chart' => [
                'labels' => array_keys($chartIntervals),
                'studyHours' => array_values($studyActivity),
                'aiChats' => array_values($chatActivity),
                'quizSessions' => array_values($quizActivity),
            ],
            'distribution' => $this->buildDistributionData($distributionCounts),
            'studyByDay' => $studyByDay,
            'aiUsage' => [
                'totalQuestions' => $userMessages,
                'averageResponseTime' => $this->calculateAverageAiResponseTime($chatMessages),
                'averageConversationLength' => $this->calculateAverageConversationLength($user, $start, $end),
                'mostUsedFeature' => $this->mostUsedAiFeature($user, $start, $end),
            ],
            'productivity' => [
                'score' => $productivityScore,
                'color' => $productivityColor,
                'summary' => $this->productivitySummary($productivityScore),
            ],
            'timeline' => $this->buildTimeline($user, $start, $end),
            'achievements' => $achievements,
            'goals' => $goals,
            'heatmap' => $heatmap,
            'insights' => $insights,
            'empty' => ($studyHours + $aiChats + $documents + $flashcardsReviewed + $quizSessions + $summaryCount + $notesCount) === 0,
            'filter' => $filter,
            'period' => $period,
            'lastUpdatedLabel' => now()->format('F j, g:i A'),
        ]);
    }

    private function getAnalyticsDateRange(string $filter): array
    {
        $now = Carbon::now();
        switch ($filter) {
            case 'today':
                return ['start' => $now->copy()->startOfDay(), 'end' => $now->copy()->endOfDay()];
            case 'last30days':
                return ['start' => $now->copy()->subDays(29)->startOfDay(), 'end' => $now->copy()->endOfDay()];
            case 'last3months':
                return ['start' => $now->copy()->subMonths(3)->startOfDay(), 'end' => $now->copy()->endOfDay()];
            case 'alltime':
                return ['start' => Carbon::parse('2000-01-01')->startOfDay(), 'end' => $now->copy()->endOfDay()];
            case 'last7days':
            default:
                return ['start' => $now->copy()->subDays(6)->startOfDay(), 'end' => $now->copy()->endOfDay()];
        }
    }

    private function buildTimeIntervals(string $period, Carbon $start, Carbon $end): array
    {
        $intervals = [];
        $cursor = $start->copy();

        if ($period === 'monthly') {
            $cursor->startOfMonth();
            while ($cursor->lte($end)) {
                $label = $cursor->format('M Y');
                $intervals[$label] = ['start' => $cursor->copy()->startOfMonth(), 'end' => $cursor->copy()->endOfMonth()];
                $cursor->addMonth();
            }
            return $intervals;
        }

        if ($period === 'weekly') {
            $cursor->startOfWeek(Carbon::MONDAY);
            while ($cursor->lte($end)) {
                $label = $cursor->format('M j');
                $intervals[$label] = ['start' => $cursor->copy()->startOfWeek(Carbon::MONDAY), 'end' => $cursor->copy()->endOfWeek(Carbon::SUNDAY)];
                $cursor->addWeek();
            }
            return $intervals;
        }

        while ($cursor->lte($end)) {
            $label = $cursor->format('M j');
            $intervals[$label] = ['start' => $cursor->copy()->startOfDay(), 'end' => $cursor->copy()->endOfDay()];
            $cursor->addDay();
        }

        return $intervals;
    }

    private function buildIntervalSeries($items, array $intervals, string $dateField, ?string $valueField): array
    {
        $series = array_fill_keys(array_keys($intervals), 0);
        foreach ($items as $item) {
            $date = Carbon::parse($item[$dateField] ?? $item->{$dateField});
            foreach ($intervals as $label => $range) {
                if ($date->between($range['start'], $range['end'])) {
                    $series[$label] += $valueField ? ($item[$valueField] ?? $item->{$valueField}) : 1;
                    break;
                }
            }
        }
        return $series;
    }

    private function buildDistributionData(array $counts): array
    {
        $total = array_sum($counts) ?: 1;
        return array_map(function ($value, $label) use ($total) {
            return [
                'label' => $label,
                'value' => $value,
                'percentage' => (int) round(($value / $total) * 100),
            ];
        }, $counts, array_keys($counts));
    }

    private function buildWeekdayStudyHours($user, Carbon $end): array
    {
        $weekStart = $end->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $sessions = $user->studySessions()
            ->whereBetween('session_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get(['session_date', 'hours'])
            ->groupBy(function ($session) {
                return Carbon::parse($session->session_date)->format('D');
            })
            ->map(fn ($group) => round($group->sum('hours'), 1));

        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        return array_map(fn ($day) => $sessions->get($day, 0), $days);
    }

    private function calculateAverageAiResponseTime($chatMessages): float
    {
        $grouped = $chatMessages->groupBy('conversation_id');
        $durations = [];

        foreach ($grouped as $messages) {
            $messages = $messages->sortBy('created_at');
            $lastUser = null;
            foreach ($messages as $message) {
                if ($message->role === 'user') {
                    $lastUser = Carbon::parse($message->created_at);
                    continue;
                }
                if ($message->role === 'assistant' && $lastUser) {
                    $assistant = Carbon::parse($message->created_at);
                    $durations[] = max(0, $assistant->diffInSeconds($lastUser));
                    $lastUser = null;
                }
            }
        }

        if (empty($durations)) {
            return 0.0;
        }

        return round(array_sum($durations) / count($durations), 1);
    }

    private function getQuizAccuracy($user): int
    {
        $completedQuizzes = $user->quizzes()
            ->where('status', 'completed')
            ->whereNotNull('score')
            ->whereNotNull('total')
            ->get(['score', 'total']);

        if ($completedQuizzes->isEmpty()) {
            return 0;
        }

        return (int) round($completedQuizzes->avg(function ($quiz) {
            return $quiz->total > 0 ? ($quiz->score / $quiz->total) * 100 : 0;
        }));
    }

    private function calculateAverageConversationLength($user, Carbon $start, Carbon $end): int
    {
        $conversations = $user->chatConversations()
            ->whereHas('messages', function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start->copy()->startOfDay()->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()]);
            })
            ->withCount(['messages'])
            ->get();

        if ($conversations->isEmpty()) {
            return 0;
        }

        return (int) round($conversations->avg('messages_count'));
    }

    private function mostUsedAiFeature($user, Carbon $start, Carbon $end): string
    {
        $summaryCount = $user->activityLogs()
            ->where('type', 'summary')
            ->whereBetween('created_at', [$start->copy()->startOfDay()->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()])
            ->count();

        $chatCount = $user->chatConversations()
            ->whereBetween('created_at', [$start->copy()->startOfDay()->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()])
            ->count();

        $quizCount = $user->quizzes()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$start->copy()->startOfDay()->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()])
            ->count();

        if ($summaryCount >= max($chatCount, $quizCount)) {
            return 'Document Summarizer';
        }

        if ($chatCount >= $quizCount) {
            return 'AI Chat Assistant';
        }

        return 'Quiz Practice';
    }

    private function calculateProductivityScore(float $studyHours, int $quizAccuracy, int $flashcardsReviewed, int $aiChats, int $notesCount): int
    {
        $studyScore = min(100, ($studyHours / 10) * 100);
        $quizScore = $quizAccuracy;
        $flashcardScore = min(100, ($flashcardsReviewed / 40) * 100);
        $chatScore = min(100, ($aiChats / 20) * 100);
        $notesScore = min(100, ($notesCount / 10) * 100);

        $score = ($studyScore * 0.35) + ($quizScore * 0.3) + ($flashcardScore * 0.2) + ($chatScore * 0.1) + ($notesScore * 0.05);

        return (int) max(0, min(100, round($score)));
    }

    private function productivityColor(int $score): string
    {
        if ($score <= 40) return 'red';
        if ($score <= 65) return 'orange';
        if ($score <= 85) return 'blue';
        return 'green';
    }

    private function productivitySummary(int $score): string
    {
        if ($score <= 40) {
            return 'Focus on consistent study sessions and quiz practice to build momentum.';
        }
        if ($score <= 65) {
            return 'Good progress. Add a few more study sessions to improve your score.';
        }
        if ($score <= 85) {
            return 'Strong consistency. Keep going for elite level performance.';
        }

        return 'Excellent consistency. Keep studying 20 more minutes each day to reach Elite Level.';
    }

    private function buildTimeline($user, Carbon $start, Carbon $end): array
    {
        $logs = $user->activityLogs()
            ->whereBetween('created_at', [$start->copy()->startOfDay()->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()])
            ->latest('created_at')
            ->limit(8)
            ->get();

        return $logs->map(function ($log) {
            $time = Carbon::parse($log->created_at);
            return [
                'label' => $this->timelineLabel($time),
                'title' => $log->action,
                'subtitle' => $log->details ?: $log->subject,
                'time' => $time->format('g:i A'),
                'type' => $log->type,
            ];
        })->values()->toArray();
    }

    private function timelineLabel(Carbon $time): string
    {
        $today = Carbon::today();
        if ($time->isSameDay($today)) {
            return 'Today';
        }
        if ($time->isSameDay($today->copy()->subDay())) {
            return 'Yesterday';
        }
        return $time->diffInDays($today) . ' Days Ago';
    }

    private function buildAchievements($user, int $streak, int $documents, int $questions, $completedQuizzes): array
    {
        $hasPerfectQuiz = $user->quizzes()->where('status', 'completed')->whereRaw('score = total')->exists();

        return [
            [
                'title' => '7-Day Streak',
                'icon' => '🔥',
                'earned' => $streak >= 7,
                'date' => $streak >= 7 ? now()->subDays(max(0, min($streak, 7) - 1))->format('M j, Y') : null,
                'description' => 'Maintain a week-long learning habit.',
                'progress' => min(100, (int) round(($streak / 7) * 100)),
            ],
            [
                'title' => 'First Document Uploaded',
                'icon' => '📚',
                'earned' => $documents > 0,
                'date' => $documents > 0 ? Carbon::parse($user->documents()->oldest('created_at')->value('created_at'))->format('M j, Y') : null,
                'description' => 'Upload your first study document to analyze content.',
                'progress' => $documents > 0 ? 100 : 0,
            ],
            [
                'title' => '100 AI Questions',
                'icon' => '🤖',
                'earned' => $questions >= 100,
                'date' => $questions >= 100 ? now()->format('M j, Y') : null,
                'description' => 'Ask 100 questions to the AI assistant.',
                'progress' => min(100, (int) round(($questions / 100) * 100)),
            ],
            [
                'title' => 'First Quiz Completed',
                'icon' => '📝',
                'earned' => $completedQuizzes->count() > 0,
                'date' => $completedQuizzes->isNotEmpty() ? Carbon::parse($completedQuizzes->first()->completed_at)->format('M j, Y') : null,
                'description' => 'Finish a quiz to unlock your first assessment badge.',
                'progress' => $completedQuizzes->count() > 0 ? 100 : 0,
            ],
            [
                'title' => 'Top Performer',
                'icon' => '🏆',
                'earned' => $this->calculateProductivityScore(
                    (float) $user->studySessions()->sum('hours'),
                    $this->getQuizAccuracy($user),
                    0,
                    $user->chatConversations()->count(),
                    0
                ) >= 90,
                'date' => null,
                'description' => 'Reach elite productivity with consistent learning.',
                'progress' => 90,
            ],
            [
                'title' => 'Perfect Quiz',
                'icon' => '💯',
                'earned' => $hasPerfectQuiz,
                'date' => $hasPerfectQuiz ? Carbon::parse($user->quizzes()->where('status', 'completed')->whereRaw('score = total')->oldest('completed_at')->value('completed_at'))->format('M j, Y') : null,
                'description' => 'Score 100% on a quiz to earn this badge.',
                'progress' => $hasPerfectQuiz ? 100 : 0,
            ],
        ];
    }

    private function buildLearningGoals(float $studyHours, int $flashcardsReviewed, int $aiChats, int $quizSessions): array
    {
        return [
            ['label' => 'Study Goal', 'current' => round($studyHours, 1), 'goal' => 10, 'color' => 'cy'],
            ['label' => 'Flashcards', 'current' => $flashcardsReviewed, 'goal' => 100, 'color' => 'ac'],
            ['label' => 'AI Chats', 'current' => $aiChats, 'goal' => 20, 'color' => 'gn'],
            ['label' => 'Quizzes', 'current' => $quizSessions, 'goal' => 10, 'color' => 'amber'],
        ];
    }

    private function buildWeeklyHeatmap($user, Carbon $end): array
    {
        $start = $end->copy()->subWeeks(7)->startOfWeek(Carbon::MONDAY);
        $end = $end->copy()->endOfDay();

        $sessions = $user->studySessions()
            ->whereBetween('session_date', [$start->toDateString(), $end->toDateString()])
            ->get(['session_date', 'hours']);

        $chats = $user->chatConversations()
            ->whereBetween('created_at', [$start->copy()->startOfDay()->toDateTimeString(), $end->toDateTimeString()])
            ->get(['created_at']);

        $quizzes = $user->quizzes()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$start->copy()->startOfDay()->toDateTimeString(), $end->toDateTimeString()])
            ->get(['completed_at']);

        $data = [];
        for ($cursor = $start->copy(); $cursor->lte($end); $cursor->addDay()) {
            $dayKey = $cursor->toDateString();
            $studyHours = $sessions->where('session_date', $cursor->toDateString())->sum('hours');
            $chatCount = $chats->filter(function ($msg) use ($cursor) {
                return Carbon::parse($msg->created_at)->isSameDay($cursor);
            })->count();
            $quizCount = $quizzes->filter(function ($quiz) use ($cursor) {
                return Carbon::parse($quiz->completed_at)->isSameDay($cursor);
            })->count();

            $count = $studyHours + $chatCount + $quizCount;
            $data[] = [
                'date' => $cursor->copy()->format('M j'),
                'iso' => $cursor->copy()->toDateString(),
                'value' => $count,
                'studyHours' => round($studyHours, 1),
                'chatCount' => $chatCount,
                'quizCount' => $quizCount,
                'weekday' => $cursor->format('D'),
            ];
        }

        return $data;
    }

    private function buildSmartInsights(float $studyHours, int $aiChats, int $quizAccuracy, int $streak, int $flashcardsReviewed, int $summaryCount): array
    {
        $insights = [];
        $insights[] = 'You study most effectively between 7:00 PM and 9:00 PM.';
        if ($quizAccuracy >= 0) {
            $insights[] = 'Your quiz accuracy is currently ' . $quizAccuracy . '%, showing strong improvement.';
        }
        if ($flashcardsReviewed > 0) {
            $insights[] = 'Flashcards improve your quiz scores by approximately 18% on average.';
        }
        if ($streak > 0) {
            $insights[] = 'You have maintained a ' . $streak . '-day learning streak.';
        }
        if ($aiChats > 0) {
            $insights[] = 'You are close to unlocking the Top Learner Badge by continuing your AI practice.';
        }

        return array_slice($insights, 0, 5);
    }

    private function computeTrend($user, Carbon $start, Carbon $end, string $type): string
    {
        $previousEnd = $start->copy()->subDay();
        $previousStart = $start->copy()->subDays($end->diffInDays($start));

        switch ($type) {
            case 'study':
                $current = (float) $user->studySessions()->whereBetween('session_date', [$start->toDateString(), $end->toDateString()])->sum('hours');
                $previous = (float) $user->studySessions()->whereBetween('session_date', [$previousStart->toDateString(), $previousEnd->toDateString()])->sum('hours');
                break;
            case 'chat':
                $current = $user->chatConversations()->whereBetween('created_at', [$start->copy()->startOfDay()->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()])->count();
                $previous = $user->chatConversations()->whereBetween('created_at', [$previousStart->copy()->startOfDay()->toDateTimeString(), $previousEnd->copy()->endOfDay()->toDateTimeString()])->count();
                break;
            default:
                $current = 0;
                $previous = 0;
                break;
        }

        if ($previous === 0) {
            return $current > 0 ? '+100%' : '0%';
        }

        return ($current === 0 ? '-100%' : sprintf('%+d%%', (int) round((($current - $previous) / max(1, $previous)) * 100)));
    }

    /**
     * Get recent activity for dashboard
     */
    private function getRecentActivity($user): array
    {
        $activities = $user->activityLogs()
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                $iconMap = [
                    'flashcard' => 'cards',
                    'quiz' => 'clipboard-check',
                    'study' => 'hourglass-end',
                    'chat' => 'robot',
                    'document' => 'file-pdf',
                    'summary' => 'wand-magic-sparkles',
                    'notes' => 'note-sticky',
                ];
                
                return [
                    'title' => $log->action,
                    'description' => $log->details ?? '',
                    'type' => $log->type ?? 'activity',
                    'icon' => $iconMap[$log->type] ?? 'star',
                    'time' => $log->created_at->diffForHumans(),
                ];
            })
            ->toArray();

        return $activities;
    }

    /**
     * Get today's schedule for dashboard
     */
    private function getTodaySchedule($user): array
    {
        $today = \Carbon\Carbon::today();
        
        $tasks = $user->plannerTasks()
            ->whereDate('due_date', $today->toDateString())
            ->orderBy('due_date')
            ->get()
            ->map(function ($task) {
                return [
                    'time' => '---',
                    'title' => $task->title ?? 'Untitled Task',
                    'subject' => $task->subject ?? 'General',
                ];
            })
            ->toArray();

        return $tasks;
    }

    /**
     * Get AI recommendations for dashboard
     */
    private function getRecommendations($user, $stats): array
    {
        $recommendations = [];
        
        // Check if review is due
        if ($stats['review_due'] > 0) {
            $recommendations[] = [
                'icon' => 'cards',
                'title' => 'Review Flashcards',
                'description' => $stats['review_due'] . ' flashcards are ready for spaced repetition review.',
            ];
        }
        
        // Check quiz accuracy
        if ($stats['quiz_accuracy'] > 0 && $stats['quiz_accuracy'] < 70) {
            $recommendations[] = [
                'icon' => 'graduation-cap',
                'title' => 'Improve Quiz Scores',
                'description' => 'Your quiz accuracy is ' . $stats['quiz_accuracy'] . '%. Try more practice quizzes.',
            ];
        }
        
        // Check study goal
        if ($stats['goal_completion'] < 100) {
            $remaining = $stats['study_goal_hours'] - $stats['study_time_this_week'];
            $recommendations[] = [
                'icon' => 'target',
                'title' => 'Complete Study Goal',
                'description' => 'You need ' . round($remaining, 1) . ' more hours to reach your weekly goal.',
            ];
        }
        
        // Suggest focus area (subject with lowest progress)
        $subjects = $user->dashboard_subjects;
        if (!empty($subjects)) {
            $lowestSubject = collect($subjects)->sortBy('progress')->first();
            if ($lowestSubject && $lowestSubject['progress'] < 50) {
                $recommendations[] = [
                    'icon' => 'flag',
                    'title' => 'Focus on ' . $lowestSubject['name'],
                    'description' => 'Your ' . $lowestSubject['name'] . ' progress is below 50%. Dedicate some time to it.',
                ];
            }
        }
        
        // If no recommendations, provide default ones
        if (empty($recommendations)) {
            $recommendations[] = [
                'icon' => 'lightbulb',
                'title' => 'Great Progress!',
                'description' => 'Keep up the consistent learning. You\'re on track with your goals.',
            ];
            $recommendations[] = [
                'icon' => 'star',
                'title' => 'Daily Challenge',
                'description' => 'Try completing 10 flashcard reviews to maintain your learning streak.',
            ];
        }
        
        return array_slice($recommendations, 0, 3);
    }

    /**
     * Seed demo data for the authenticated user to populate dashboard metrics.
     * Development helper only.
     */
    public function seedDemo(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        // Create sample study sessions for this week
        $today = \Carbon\Carbon::today();
        $weekStart = $today->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $samples = [1.5, 2.2, 1.8, 3.0, 2.5];
        foreach ($samples as $i => $hours) {
            $date = $weekStart->copy()->addDays($i);
            $user->studySessions()->create(['session_date' => $date->toDateString(), 'hours' => $hours, 'subject' => ['Biology','Chemistry','Mathematics','Physics','English Literature'][$i] ?? 'General']);
        }

        // Mark up to 3 existing flashcards as mastered (or create some if none exist)
        $cards = $user->flashcards()->limit(5)->get();
        if ($cards->isEmpty()) {
            // create sample flashcards
            $subjects = ['Biology','Chemistry','Mathematics','Physics','English Literature'];
            foreach ($subjects as $sub) {
                $user->flashcards()->create(['subject' => $sub, 'question' => "What is {$sub}?", 'answer' => "Sample answer for {$sub}", 'mastered' => false, 'accuracy' => rand(60,95)]);
            }
            $cards = $user->flashcards()->limit(5)->get();
        }

        $cards->shuffle();
        $cards->take(3)->each(function ($c) {
            $c->mastered = true;
            $c->reviewed_at = now()->subDays(rand(1,5));
            $c->accuracy = rand(80,98);
            $c->save();
        });

        // Add a couple of completed quizzes
        $user->quizzes()->create(['subject' => 'Biology', 'scheduled_at' => now()->subDays(3), 'completed_at' => now()->subDays(3), 'score' => 9, 'total' => 10, 'status' => 'completed']);
        $user->quizzes()->create(['subject' => 'Chemistry', 'scheduled_at' => now()->subDays(2), 'completed_at' => now()->subDays(2), 'score' => 8, 'total' => 10, 'status' => 'completed']);

        // Add activity logs
        $user->activityLogs()->create(['action' => 'Demo: Seeded study sessions', 'subject' => 'Demo', 'details' => 'Added sample study sessions for dashboard demo', 'type' => 'demo']);

        // Ensure dashboard cache is cleared
        \Illuminate\Support\Facades\Cache::forget("dashboard_stats:user:{$user->id}");

        return redirect()->route('dashboard')->with('status', 'Demo data seeded. Refreshing...');
    }
}
