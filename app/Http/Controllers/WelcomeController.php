<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Document;
use App\Models\HelpFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class WelcomeController extends Controller
{
    /**
     * Render the welcome page with server-side stats so the hero section appears immediately.
     */
    public function index()
    {
        return view('welcome', ['welcomeStats' => $this->buildStatistics()]);
    }

    /**
     * Get welcome page statistics.
     * Returns aggregated data for the welcome page hero and stats sections.
     */
    public function getStatistics(): JsonResponse
    {
        return response()->json($this->buildStatistics());
    }

    private function buildStatistics(): array
    {
        try {
            $totalUsers = User::count();
            $totalDocuments = Document::count();
            $aiAccuracy = $this->calculateAiAccuracy();

            $averageRating = 4.8;
            if (Schema::hasTable('help_feedback')) {
                $averageRating = HelpFeedback::avg('rating') ?? 4.8;
            }

            $featuredUsers = [];
            $usersWithAvatars = User::whereNotNull('provider_avatar')
                ->latest()
                ->limit(3)
                ->get(['id', 'provider_avatar']);

            foreach ($usersWithAvatars as $user) {
                $featuredUsers[] = $user->provider_avatar;
            }

            if (count($featuredUsers) < 3) {
                $existingUserIds = $usersWithAvatars->pluck('id')->toArray();
                $remainingUsers = User::whereNotIn('id', $existingUserIds)
                    ->latest()
                    ->limit(3 - count($featuredUsers))
                    ->pluck('id')
                    ->toArray();

                foreach ($remainingUsers as $userId) {
                    $featuredUsers[] = $this->generatePlaceholderAvatar($userId);
                }
            }

            $testimonials = [];
            if (Schema::hasTable('help_feedback')) {
                $testimonials = HelpFeedback::where('rating', '>=', 4)
                    ->whereNotNull('comment')
                    ->where('comment', '!=', '')
                    ->with('user')
                    ->latest()
                    ->limit(3)
                    ->get()
                    ->map(function ($feedback) {
                        return [
                            'rating' => $feedback->rating,
                            'comment' => $feedback->comment,
                            'user_name' => $feedback->user->name ?? 'Student',
                            'user_avatar' => $feedback->user->provider_avatar ?? $this->generatePlaceholderAvatar($feedback->user->id),
                        ];
                    })->toArray();
            }

            if (empty($testimonials)) {
                $testimonials = $this->generateSampleTestimonials();
            }

            return [
                'total_users' => $this->formatLargeNumber($totalUsers),
                'total_users_raw' => $totalUsers,
                'total_documents' => $this->formatLargeNumber($totalDocuments),
                'total_documents_raw' => $totalDocuments,
                'ai_accuracy' => $aiAccuracy,
                'average_rating' => round($averageRating, 1),
                'featured_users' => array_slice($featuredUsers, 0, 3),
                'testimonials' => $testimonials,
            ];
        } catch (\Exception $e) {
            \Log::error('Welcome statistics error: ' . $e->getMessage());

            return [
                'total_users' => '0',
                'total_users_raw' => 0,
                'total_documents' => '0',
                'total_documents_raw' => 0,
                'ai_accuracy' => 0,
                'average_rating' => 4.8,
                'featured_users' => [],
                'testimonials' => $this->generateSampleTestimonials(),
            ];
        }
    }

    /**
     * Calculate AI accuracy percentage based on successful document processing.
     */
    private function calculateAiAccuracy(): int
    {
        $totalDocuments = Document::count();

        if ($totalDocuments === 0) {
            return 98; // Default fallback
        }

        // Count documents with successful summaries (non-null and non-empty)
        $successfulDocuments = Document::whereNotNull('summary')
            ->where('summary', '!=', '')
            ->count();

        $accuracy = (int) ($successfulDocuments / $totalDocuments * 100);

        return min($accuracy, 100); // Cap at 100%
    }

    /**
     * Format large numbers with K, M, B suffixes.
     */
    private function formatLargeNumber($number): string
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 0) . 'K';
        }

        return (string) $number;
    }

    /**
     * Generate a deterministic placeholder avatar URL based on user ID.
     */
    private function generatePlaceholderAvatar($userId): string
    {
        // Using gravatar with MD5 of user ID for consistency
        $hash = md5('user' . $userId);

        return "https://i.gravatar.com/avatar/{$hash}?s=48&d=identicon";
    }

    /**
     * Generate sample testimonials if no real ones exist.
     */
    private function generateSampleTestimonials(): array
    {
        return [
            [
                'rating' => 5,
                'comment' => 'SHEELEARN cut my study time in half. The AI summaries are incredibly accurate and the flashcards helped me ace my biology finals.',
                'user_name' => 'Sarah M.',
                'user_avatar' => 'https://i.gravatar.com/avatar/user1?s=48&d=identicon',
            ],
            [
                'rating' => 5,
                'comment' => 'Chatting with my PDFs is a game changer. I can ask questions about specific paragraphs and get instant, accurate answers.',
                'user_name' => 'James K.',
                'user_avatar' => 'https://i.gravatar.com/avatar/user2?s=48&d=identicon',
            ],
            [
                'rating' => 5,
                'comment' => 'The study planner alone is worth it. It adapts to my schedule and pace. My grades went from B\'s to A\'s in one semester.',
                'user_name' => 'Priya R.',
                'user_avatar' => 'https://i.gravatar.com/avatar/user3?s=48&d=identicon',
            ],
        ];
    }
}
