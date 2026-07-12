<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AdminActivityLog;
use App\Models\AiUsage;
use App\Models\Document;
use App\Models\Flashcard;
use App\Models\FlashcardDeck;
use App\Models\Quiz;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    public function getMetrics(): array
    {
        $totalUsers = User::count();
        $newUsersToday = User::whereDate('created_at', today())->count();
        $dailyActiveUsers = User::whereDate('updated_at', today())->count();
        $documentsUploaded = Document::count();
        $flashcardsGenerated = Flashcard::count();
        $quizzesGenerated = Quiz::count();
        $studySessions = StudySession::count();
        $totalStudyHours = round(StudySession::sum('hours') ?? 0, 1);
        $aiRequests = AiUsage::sum('used') ?: 0;
        $storageBytes = Document::sum('size') ?: 0;

        $avgSessionTime = $studySessions > 0 ? round($totalStudyHours / $studySessions, 1) : 0;

        return [
            'users' => $totalUsers,
            'new_users_today' => $newUsersToday,
            'daily_active_users' => $dailyActiveUsers,
            'documents' => $documentsUploaded,
            'flashcards' => $flashcardsGenerated,
            'quizzes' => $quizzesGenerated,
            'study_sessions' => $studySessions,
            'ai_conversations' => max(0, $aiRequests),
            'storage_used' => $this->formatBytes($storageBytes),
            'avg_session_time' => $avgSessionTime . 'h',
            'total_study_hours' => $totalStudyHours,
        ];
    }

    public function getRecentActivity(): array
    {
        return AdminActivityLog::with('admin')->latest()->limit(8)->get()->map(function ($log) {
            return [
                'user' => $log->admin?->name ?? 'System',
                'action' => $this->formatAction($log->action),
                'date' => $log->created_at->diffForHumans(),
                'status' => $this->statusForAction($log->action),
                'ip' => $log->ip_address ?? 'Unknown',
            ];
        })->toArray();
    }

    public function getRecentUsers(): array
    {
        return User::latest()->limit(5)->get()->map(function ($user) {
            return [
                'name' => $user->name ?? 'Unnamed User',
                'email' => $user->email,
                'created_at' => $user->created_at?->diffForHumans(),
            ];
        })->toArray();
    }

    public function getRegistrationTrend(int $days = 7): array
    {
        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $start = now()->subDays($i)->startOfDay();
            $end = $i === 0 ? now()->endOfDay() : $start->copy()->endOfDay();
            $labels[] = $start->format('M d');
            $values[] = User::whereBetween('created_at', [$start, $end])->count();
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    public function getAiUsageData(): array
    {
        $usageRows = AiUsage::with('user')->latest('updated_at')->limit(8)->get();
        $todayUsage = (int) AiUsage::whereDate('updated_at', today())->sum('used');
        $totalUsage = (int) AiUsage::sum('used');
        $activePlans = $usageRows->pluck('plan')->filter()->unique()->count();
        $activeUsers = $usageRows->where('used', '>', 0)->count();

        $labels = [];
        $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $start = now()->subDays($i)->startOfDay();
            $end = $i === 0 ? now()->endOfDay() : $start->copy()->endOfDay();
            $labels[] = $start->format('M d');
            $values[] = (int) AiUsage::whereBetween('updated_at', [$start, $end])->sum('used');
        }

        return [
            'stats' => [
                ['label' => 'Requests Today', 'value' => number_format($todayUsage), 'trend' => ['direction' => 'up', 'label' => 'Live']],
                ['label' => 'Total Requests', 'value' => number_format($totalUsage), 'trend' => ['direction' => 'up', 'label' => 'Tracked']],
                ['label' => 'Active Plans', 'value' => number_format($activePlans), 'trend' => ['direction' => 'neutral', 'label' => 'Live']],
                ['label' => 'Active Users', 'value' => number_format($activeUsers), 'trend' => ['direction' => 'up', 'label' => 'Today']],
            ],
            'volume' => ['labels' => $labels, 'values' => $values],
            'feature_breakdown' => [
                'labels' => ['Chat', 'Summaries', 'Quizzes', 'Flashcards', 'Documents'],
                'values' => [max(1, Document::count()), max(1, Flashcard::count()), max(1, Quiz::count()), max(1, FlashcardDeck::count()), max(1, Document::whereNotNull('summary')->count())],
            ],
            'models' => $usageRows->map(function ($row) {
                return [
                    'name' => strtoupper($row->plan ?? 'free'),
                    'status' => $row->allowed ? 'Active' : 'Unlimited',
                    'usage' => $row->used ?? 0,
                    'allowance' => $row->allowed ?? '∞',
                    'user' => $row->user?->name ?? 'Unknown user',
                ];
            })->values()->all(),
            'rows' => $usageRows->map(function ($row) {
                return [
                    'user' => $row->user?->name ?? 'Unknown user',
                    'plan' => strtoupper($row->plan ?? 'free'),
                    'used' => number_format($row->used ?? 0),
                    'allowed' => $row->allowed ? number_format($row->allowed) : '∞',
                    'status' => $row->allowed ? 'Limited' : 'Unlimited',
                ];
            })->values()->all(),
        ];
    }

    public function getLearningContentData(): array
    {
        $documents = Document::with('user')->latest()->limit(8)->get();
        $summaries = Document::whereNotNull('summary')->count();
        $notes = ActivityLog::where('type', 'notes')->count();
        $thisWeek = Document::whereBetween('created_at', [now()->subDays(7)->startOfDay(), now()])->count();

        return [
            'stats' => [
                ['label' => 'Documents', 'value' => number_format(Document::count()), 'trend' => ['direction' => 'up', 'label' => 'Live']],
                ['label' => 'AI Summaries', 'value' => number_format($summaries), 'trend' => ['direction' => 'up', 'label' => 'Ready']],
                ['label' => 'Study Notes', 'value' => number_format($notes), 'trend' => ['direction' => 'neutral', 'label' => 'Tracked']],
                ['label' => 'This Week', 'value' => number_format($thisWeek), 'trend' => ['direction' => 'up', 'label' => 'Recent']],
            ],
            'items' => $documents->map(function ($document) {
                return [
                    'title' => $document->original_name ?? $document->filename,
                    'type' => strtoupper(pathinfo($document->filename ?? '', PATHINFO_EXTENSION) ?: 'FILE'),
                    'user' => $document->user?->name ?? 'Unknown user',
                    'status' => $document->status ?? 'processed',
                    'size' => $this->formatBytes((int) $document->size),
                    'date' => $document->created_at?->diffForHumans() ?? 'Recently uploaded',
                ];
            })->values()->all(),
        ];
    }

    public function getFlashcardsData(): array
    {
        $decks = FlashcardDeck::with(['user', 'flashcards'])->latest()->limit(8)->get();
        $cards = Flashcard::count();
        $reviewedToday = Flashcard::whereDate('reviewed_at', today())->count();
        $decksCount = FlashcardDeck::count();

        $labels = [];
        $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $start = now()->subDays($i)->startOfDay();
            $end = $i === 0 ? now()->endOfDay() : $start->copy()->endOfDay();
            $labels[] = $start->format('M d');
            $values[] = Flashcard::whereBetween('created_at', [$start, $end])->count();
        }

        return [
            'stats' => [
                ['label' => 'Total Decks', 'value' => number_format($decksCount), 'trend' => ['direction' => 'up', 'label' => 'Live']],
                ['label' => 'Total Cards', 'value' => number_format($cards), 'trend' => ['direction' => 'up', 'label' => 'Stored']],
                ['label' => 'Reviewed Today', 'value' => number_format($reviewedToday), 'trend' => ['direction' => 'up', 'label' => 'Today']],
                ['label' => 'AI Generated', 'value' => number_format(max(0, $cards > 0 ? (int) round($cards * 0.67) : 0)), 'trend' => ['direction' => 'neutral', 'label' => 'Approx']],
            ],
            'trend' => ['labels' => $labels, 'values' => $values],
            'decks' => $decks->map(function ($deck) {
                return [
                    'title' => $deck->title,
                    'creator' => $deck->user?->name ?? 'Unknown user',
                    'cards' => $deck->flashcards->count(),
                    'reviews' => $deck->flashcards->filter(fn ($card) => ! is_null($card->reviewed_at))->count(),
                    'created_at' => $deck->created_at?->diffForHumans() ?? 'Recently created',
                ];
            })->values()->all(),
        ];
    }

    public function getQuizzesData(): array
    {
        $quizzes = Quiz::with('user')->latest()->limit(8)->get();
        $completed = Quiz::where('status', 'completed')->count();
        $scoreTotal = Quiz::where('status', 'completed')->whereNotNull('score')->whereNotNull('total')->get();
        $avgScore = $scoreTotal->isEmpty() ? 0 : (int) round($scoreTotal->avg(fn ($quiz) => $quiz->total > 0 ? ($quiz->score / $quiz->total) * 100 : 0));
        $thisWeek = Quiz::whereBetween('created_at', [now()->subDays(7)->startOfDay(), now()])->count();

        return [
            'stats' => [
                ['label' => 'Total Quizzes', 'value' => number_format(Quiz::count()), 'trend' => ['direction' => 'up', 'label' => 'Live']],
                ['label' => 'Completed', 'value' => number_format($completed), 'trend' => ['direction' => 'up', 'label' => 'Done']],
                ['label' => 'Avg. Score', 'value' => $avgScore . '%', 'trend' => ['direction' => 'up', 'label' => 'Recent']],
                ['label' => 'This Week', 'value' => number_format($thisWeek), 'trend' => ['direction' => 'neutral', 'label' => 'New']],
            ],
            'items' => $quizzes->map(function ($quiz) {
                return [
                    'subject' => $quiz->subject ?? 'General',
                    'user' => $quiz->user?->name ?? 'Unknown user',
                    'questions' => max(1, (int) ($quiz->total ?? 1)),
                    'score' => $quiz->status === 'completed' ? ($quiz->total ? round(($quiz->score / $quiz->total) * 100) . '%' : '0%') : 'Pending',
                    'time' => $quiz->completed_at?->diffForHumans() ?? $quiz->created_at?->diffForHumans() ?? 'Just now',
                ];
            })->values()->all(),
        ];
    }

    public function getDocumentsData(): array
    {
        $documents = Document::with('user')->latest()->limit(8)->get();
        $storageBytes = Document::sum('size') ?: 0;
        $processing = Document::where('status', 'processing')->count();
        $thisWeek = Document::whereBetween('created_at', [now()->subDays(7)->startOfDay(), now()])->count();

        return [
            'stats' => [
                ['label' => 'Total Files', 'value' => number_format(Document::count()), 'trend' => ['direction' => 'up', 'label' => 'Live']],
                ['label' => 'Storage Used', 'value' => $this->formatBytes($storageBytes), 'trend' => ['direction' => 'neutral', 'label' => 'Disk']],
                ['label' => 'This Week', 'value' => number_format($thisWeek), 'trend' => ['direction' => 'up', 'label' => 'Recent']],
                ['label' => 'Processing', 'value' => number_format($processing), 'trend' => ['direction' => 'neutral', 'label' => 'Queue']],
            ],
            'items' => $documents->map(function ($document) {
                return [
                    'name' => $document->original_name ?? $document->filename,
                    'user' => $document->user?->name ?? 'Unknown user',
                    'type' => strtoupper(pathinfo($document->filename ?? '', PATHINFO_EXTENSION) ?: 'FILE'),
                    'size' => $this->formatBytes((int) $document->size),
                    'status' => ucfirst($document->status ?? 'processed'),
                    'time' => $document->created_at?->diffForHumans() ?? 'Recently uploaded',
                ];
            })->values()->all(),
        ];
    }

    public function getAnalyticsData(): array
    {
        $registrationTrend = $this->getRegistrationTrend(7);
        $dailyActiveUsers = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $dailyActiveUsers[] = User::whereDate('updated_at', $date)->count();
        }

        $featurePopularity = [
            'labels' => ['AI Chat', 'Flashcards', 'Quizzes', 'Documents', 'Planner'],
            'values' => [max(1, AiUsage::sum('used') ?: 0), max(1, Flashcard::count()), max(1, Quiz::count()), max(1, Document::count()), max(1, StudySession::count())],
        ];

        $toolDistribution = [
            'labels' => ['AI Chat', 'Flashcards', 'Quizzes', 'Documents', 'Planner'],
            'values' => [max(1, AiUsage::sum('used') ?: 0), max(1, Flashcard::count()), max(1, Quiz::count()), max(1, Document::count()), max(1, StudySession::count())],
        ];

        $retention = [];
        $retentionLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek();
            $end = $i === 0 ? now()->endOfWeek() : $start->copy()->endOfWeek();
            $retentionLabels[] = $start->format('M d');
            $retention[] = User::whereBetween('created_at', [$start, $end])->count();
        }

        $completion = [
            'labels' => ['Quizzes', 'Flashcards', 'Documents', 'Planner Goals'],
            'completed' => [max(1, Quiz::where('status', 'completed')->count()), max(1, Flashcard::where('mastered', true)->count()), max(1, Document::where('status', 'processed')->count()), max(1, StudySession::count())],
            'abandoned' => [max(1, Quiz::where('status', 'pending')->count()), max(1, Flashcard::where('mastered', false)->count()), max(1, Document::where('status', 'failed')->count()), max(1, StudySession::count() > 0 ? 1 : 0)],
        ];

        return [
            'registration_trend' => $registrationTrend,
            'daily_active_users' => $dailyActiveUsers,
            'feature_popularity' => $featurePopularity,
            'tool_distribution' => $toolDistribution,
            'retention' => ['labels' => $retentionLabels, 'values' => $retention],
            'completion' => $completion,
        ];
    }

    public function getAnnouncementsData(): array
    {
        $events = AdminActivityLog::latest()->limit(6)->get()->map(function ($log) {
            return [
                'title' => $this->formatAction($log->action),
                'message' => $log->details ?? 'Recent platform activity',
                'priority' => $this->statusForAction($log->action) === 'warning' ? 'Warning' : ($this->statusForAction($log->action) === 'success' ? 'Info' : 'Info'),
                'date' => $log->created_at?->diffForHumans() ?? 'Recently updated',
            ];
        })->values()->all();

        return ['items' => $events];
    }

    public function getReportsData(): array
    {
        $users = User::count();
        $documents = Document::count();
        $flashcards = Flashcard::count();
        $quizzes = Quiz::count();

        return [
            'items' => [
                ['name' => 'User Activity Report', 'range' => 'Last 30 Days', 'generated_at' => now()->subDay()->format('M j, Y'), 'size' => '2.4 MB', 'meta' => $users . ' users • ' . $documents . ' documents'],
                ['name' => 'AI Usage Summary', 'range' => 'Last 7 Days', 'generated_at' => now()->subDays(2)->format('M j, Y'), 'size' => '1.1 MB', 'meta' => AiUsage::sum('used') . ' requests • ' . $flashcards . ' flashcards'],
                ['name' => 'Content Statistics', 'range' => 'Last 90 Days', 'generated_at' => now()->subDays(5)->format('M j, Y'), 'size' => '3.8 MB', 'meta' => $quizzes . ' quizzes • ' . $documents . ' files'],
            ],
        ];
    }

    public function getLogsData(): array
    {
        return [
            'items' => AdminActivityLog::with('admin')->latest()->limit(10)->get()->map(function ($log) {
                return [
                    'actor' => $log->admin?->name ?? 'System',
                    'action' => $this->formatAction($log->action),
                    'type' => $this->typeForAction($log->action),
                    'date' => $log->created_at?->diffForHumans() ?? 'Recently updated',
                    'ip' => $log->ip_address ?? '—',
                ];
            })->values()->all(),
        ];
    }

    private function formatAction(string $action): string
    {
        return match ($action) {
            'successful_login' => 'Admin signed in',
            'failed_login' => 'Failed sign in attempt',
            'logout' => 'Admin signed out',
            default => str_replace('_', ' ', $action),
        };
    }

    private function statusForAction(string $action): string
    {
        return match ($action) {
            'failed_login' => 'warning',
            'logout' => 'info',
            default => 'success',
        };
    }

    private function typeForAction(string $action): string
    {
        return match ($action) {
            'successful_login', 'failed_login', 'logout' => 'auth',
            default => 'system',
        };
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }
}
