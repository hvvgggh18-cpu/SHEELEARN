<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_renders_without_two_factor_route(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('settings'));

        $response->assertOk()
            ->assertSee('Two-factor authentication');
    }

    public function test_settings_page_shows_storage_usage(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Document::create([
            'user_id' => $user->id,
            'original_name' => 'notes.pdf',
            'filename' => 'documents/'.$user->id.'/notes.pdf',
            'mime' => 'application/pdf',
            'size' => 16 * 1024 * 1024,
            'status' => 'uploaded',
        ]);

        $response = $this->get(route('settings'));

        $response->assertOk()
            ->assertSee('16 MB')
            ->assertSee('500 MB');
    }
}
