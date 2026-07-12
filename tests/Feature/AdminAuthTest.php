<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AiUsage;
use App\Models\Document;
use App\Models\Flashcard;
use App\Models\FlashcardDeck;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_access_dashboard(): void
    {
        $admin = Admin::create([
            'name' => 'System Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
            'is_super_admin' => true,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_student_accounts_are_blocked_from_admin_routes(): void
    {
        $student = User::factory()->create([
            'email' => 'student@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($student, 'web');

        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }

    public function test_admin_can_view_and_toggle_users(): void
    {
        $admin = Admin::create([
            'name' => 'System Admin',
            'email' => 'admin2@example.com',
            'password' => Hash::make('secret123'),
            'is_super_admin' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Jane Doe');
        $response->assertSee('jane@example.com');
        $response->assertSee('Active');

        $toggleResponse = $this->post('/admin/users/' . $user->id . '/toggle-status');

        $toggleResponse->assertRedirect();
        $this->assertSame('suspended', $user->refresh()->status);
    }

    public function test_admin_sidebar_routes_render_their_sections(): void
    {
        $admin = Admin::create([
            'name' => 'System Admin',
            'email' => 'admin3@example.com',
            'password' => Hash::make('secret123'),
            'is_super_admin' => true,
        ]);

        $this->actingAs($admin, 'admin');

        $routes = [
            '/admin/ai-usage' => 'AI Usage',
            '/admin/learning-content' => 'Learning Content',
            '/admin/flashcards' => 'Flashcards',
            '/admin/quizzes' => 'Quizzes',
            '/admin/documents' => 'Documents',
            '/admin/announcements' => 'Announcements',
            '/admin/reports' => 'Reports',
            '/admin/profile' => 'Profile',
        ];

        foreach ($routes as $path => $title) {
            $response = $this->get($path);
            $response->assertOk();
            $response->assertSee($title);
        }
    }

    public function test_admin_section_views_use_real_database_data(): void
    {
        $admin = Admin::create([
            'name' => 'System Admin',
            'email' => 'admin4@example.com',
            'password' => Hash::make('secret123'),
            'is_super_admin' => true,
        ]);

        $student = User::factory()->create([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => Hash::make('password123'),
        ]);

        Document::create([
            'user_id' => $student->id,
            'original_name' => 'Physics Notes.pdf',
            'filename' => 'physics-notes.pdf',
            'mime' => 'application/pdf',
            'size' => 2048,
            'status' => 'processed',
        ]);

        $deck = FlashcardDeck::create([
            'user_id' => $student->id,
            'title' => 'Physics Deck',
        ]);

        Flashcard::create([
            'user_id' => $student->id,
            'deck_id' => $deck->id,
            'subject' => 'Physics',
            'question' => 'What is force?',
            'answer' => 'A push or pull.',
            'mastered' => true,
            'reviewed_at' => now(),
        ]);

        Quiz::create([
            'user_id' => $student->id,
            'subject' => 'Physics',
            'status' => 'completed',
            'score' => 18,
            'total' => 20,
            'completed_at' => now(),
        ]);

        AiUsage::create([
            'user_id' => $student->id,
            'plan' => 'pro',
            'used' => 8,
            'allowed' => 20,
        ]);

        $this->actingAs($admin, 'admin');

        $this->get('/admin/ai-usage')->assertSee('Ada Lovelace');
        $this->get('/admin/learning-content')->assertSee('Physics Notes.pdf');
        $this->get('/admin/flashcards')->assertSee('Physics Deck');
        $this->get('/admin/quizzes')->assertSee('Physics');
        $this->get('/admin/documents')->assertSee('Physics Notes.pdf');
    }
}
