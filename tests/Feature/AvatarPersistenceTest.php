<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AvatarPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_avatar_and_it_persists_across_requests(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $avatar = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $response = $this->post(route('account.avatar.upload'), [
            'avatar' => $avatar,
        ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'message', 'avatar_url', 'settings']);

        $responseData = $response->json();
        $this->assertArrayHasKey('avatar_url', $responseData);
        $this->assertStringContainsString('/storage/avatars/', $responseData['avatar_url']);
        $this->assertStringEndsWith('.jpg', $responseData['avatar_url']);

        $user->refresh();
        $this->assertNotNull($user->settings['profile_avatar']);
        $this->assertSame($responseData['avatar_url'], $user->settings['profile_avatar']);

        $uploadedPath = str_replace(Storage::disk('public')->url(''), '', $responseData['avatar_url']);
        Storage::disk('public')->assertExists($uploadedPath);

        $avatarResponse = $this->get(route('user.avatar'));
        $avatarResponse->assertOk()
            ->assertJson(['success' => true, 'avatar_url' => $responseData['avatar_url']]);
    }
}
