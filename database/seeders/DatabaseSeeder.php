<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Flashcard;
use App\Models\Quiz;
use App\Models\StudySession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminSeeder::class);

        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => bin2hex(random_bytes(10)),
            ],
        );

        if ($user->studySessions()->doesntExist() && $user->flashcards()->doesntExist() && $user->quizzes()->doesntExist() && $user->activityLogs()->doesntExist()) {
            $today = Carbon::today();
            $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);

            $sessions = [
                ['session_date' => $weekStart->toDateString(), 'hours' => 1.5, 'subject' => 'Biology'],
                ['session_date' => $weekStart->copy()->addDay()->toDateString(), 'hours' => 2.2, 'subject' => 'Chemistry'],
                ['session_date' => $weekStart->copy()->addDays(2)->toDateString(), 'hours' => 1.8, 'subject' => 'Mathematics'],
                ['session_date' => $weekStart->copy()->addDays(3)->toDateString(), 'hours' => 3.0, 'subject' => 'Physics'],
                ['session_date' => $weekStart->copy()->addDays(4)->toDateString(), 'hours' => 2.5, 'subject' => 'English Literature'],
            ];

            foreach ($sessions as $session) {
                StudySession::create(array_merge($session, ['user_id' => $user->id]));
            }

            $flashcards = [
                ['subject' => 'Biology', 'due_at' => Carbon::now()->subHours(1), 'reviewed_at' => Carbon::now()->subDays(1), 'mastered' => false, 'accuracy' => 82],
                ['subject' => 'Chemistry', 'due_at' => Carbon::now()->addHours(2), 'reviewed_at' => Carbon::now()->subDays(2), 'mastered' => false, 'accuracy' => 76],
                ['subject' => 'Mathematics', 'due_at' => Carbon::now()->subHours(3), 'reviewed_at' => Carbon::now()->subDays(1), 'mastered' => true, 'accuracy' => 95],
                ['subject' => 'Physics', 'due_at' => Carbon::now()->addDays(1), 'reviewed_at' => Carbon::now()->subDays(3), 'mastered' => false, 'accuracy' => 68],
                ['subject' => 'English Literature', 'due_at' => Carbon::now()->subHours(5), 'reviewed_at' => Carbon::now()->subDays(4), 'mastered' => true, 'accuracy' => 88],
            ];

            foreach ($flashcards as $flashcard) {
                Flashcard::create(array_merge($flashcard, ['user_id' => $user->id]));
            }

            $quizzes = [
                ['subject' => 'Biology', 'scheduled_at' => Carbon::now()->subDays(2), 'completed_at' => Carbon::now()->subDays(2), 'score' => 9, 'total' => 10, 'status' => 'completed'],
                ['subject' => 'Chemistry', 'scheduled_at' => Carbon::now()->subDays(1), 'completed_at' => Carbon::now()->subDays(1), 'score' => 8, 'total' => 10, 'status' => 'completed'],
                ['subject' => 'Physics', 'scheduled_at' => Carbon::now()->addDays(1), 'status' => 'pending'],
                ['subject' => 'Mathematics', 'scheduled_at' => Carbon::now()->addDays(3), 'status' => 'pending'],
            ];

            foreach ($quizzes as $quiz) {
                Quiz::create(array_merge($quiz, ['user_id' => $user->id]));
            }

            $activities = [
                ['action' => 'Quiz Completed', 'subject' => 'Biology', 'details' => 'Score: 9/10 · Chapter 11'],
                ['action' => 'Flashcards Reviewed', 'subject' => 'Mathematics', 'details' => '24 cards · 92% accuracy'],
                ['action' => 'Summary Generated', 'subject' => 'Chemistry', 'details' => 'Ch.9 · 2,400 → 180 words'],
                ['action' => 'PDF Uploaded', 'subject' => 'Physics', 'details' => 'thermodynamics_ch8.pdf'],
            ];

            foreach ($activities as $activity) {
                ActivityLog::create(array_merge($activity, ['user_id' => $user->id]));
            }
        }
    }
}
