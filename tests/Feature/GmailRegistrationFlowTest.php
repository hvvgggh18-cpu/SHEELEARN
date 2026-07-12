<?php

namespace Tests\Feature;

use App\Models\EmailOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GmailRegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_registration_flow_can_progress_from_otp_to_profile_completion(): void
    {
        config(['app.debug' => true]);

        $this->withSession([
            'google_registration' => [
                'provider_id' => 'google-123',
                'email' => 'student@gmail.com',
                'name' => 'Student Learner',
                'avatar' => 'https://example.com/avatar.png',
                'email_verified' => true,
            ],
        ]);

        $response = $this->postJson('/api/auth/google/otp/send');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', "We've sent a 6-digit verification code to your Gmail address.");

        $emailOtp = EmailOtp::where('email', 'student@gmail.com')->first();
        $this->assertNotNull($emailOtp);

        // Since the OTP is hashed and not exposed in the response, set a known test OTP
        $testOtp = '123456';
        $emailOtp->update([
            'otp_hash' => Hash::make($testOtp),
            'attempt_count' => 0,
        ]);

        $verifyResponse = $this->postJson('/api/auth/google/otp/verify', [
            'otp' => $testOtp,
        ]);

        $verifyResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('next_step', 'profile');

        $completeResponse = $this->postJson('/api/auth/google/profile/complete', [
            'name' => 'Updated Name',
            'username' => 'updatedstudent',
            'password' => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ]);

        $completeResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('redirect', '/dashboard');

        $this->assertDatabaseHas('users', [
            'email' => 'student@gmail.com',
            'username' => 'updatedstudent',
            'name' => 'Updated Name',
        ]);
    }

    public function test_google_profile_completion_still_succeeds_when_username_column_is_missing(): void
    {
        if (Schema::hasColumn('users', 'username')) {
            Schema::table('users', function ($table) {
                $table->dropUnique(['username']);
            });
            Schema::table('users', function ($table) {
                $table->dropColumn('username');
            });
        }

        $this->withSession([
            'google_registration' => [
                'provider_id' => 'google-456',
                'email' => 'student2@gmail.com',
                'name' => 'Student Learner',
                'avatar' => 'https://example.com/avatar.png',
                'email_verified' => true,
            ],
            'google_registration_verified' => true,
        ]);

        $response = $this->postJson('/api/auth/google/profile/complete', [
            'name' => 'Fallback Name',
            'username' => 'fallbackstudent',
            'password' => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'email' => 'student2@gmail.com',
            'name' => 'Fallback Name',
        ]);
    }
}
