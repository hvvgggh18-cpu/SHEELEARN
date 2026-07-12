<?php

namespace Tests\Feature;

use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_flow_using_email_otp(): void
    {
        config(['app.debug' => true]);

        $user = User::create([
            'name' => 'Reset User',
            'email' => 'reset@example.com',
            'password' => Hash::make('OldPass1!'),
            'plan' => 'free',
        ]);

        $response = $this->postJson('/api/auth/password-reset/request', [
            'email' => 'reset@example.com',
        ]);

        $response->assertStatus(200)->assertJsonPath('success', true);

        $emailOtp = EmailOtp::where('email', 'reset@example.com')->first();
        $this->assertNotNull($emailOtp);

        $testOtp = '654321';
        $emailOtp->update(['otp_hash' => Hash::make($testOtp), 'attempt_count' => 0]);

        $verify = $this->postJson('/api/auth/password-reset/verify', [
            'email' => 'reset@example.com',
            'otp' => $testOtp,
        ]);

        $verify->assertStatus(200)->assertJsonPath('success', true);

        $complete = $this->postJson('/api/auth/password-reset/complete', [
            'password' => 'NewPass1!',
            'password_confirmation' => 'NewPass1!',
        ]);

        $complete->assertStatus(200)->assertJsonPath('success', true);

        $this->assertTrue(Hash::check('NewPass1!', $user->fresh()->password));
    }
}
