<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Socialite\Facades\Socialite;
use Twilio\Rest\Client;

class AuthController extends Controller
{
    /**
     * API Register - Handle user registration via modal
     */
    public function apiRegister(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'plan' => config('ai_usage.default_plan', 'free'),
            ]);

            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful!',
                'user' => $user,
                'redirect' => '/dashboard'
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API Login - Handle user login via modal
     */
    public function apiLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $request->session()->regenerate();

                return response()->json([
                    'success' => true,
                    'message' => 'Login successful!',
                    'user' => Auth::user(),
                    'redirect' => '/dashboard'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.'
            ], 401);
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Redirect user to Google OAuth.
     */
    public function redirectToGoogle(Request $request)
    {
        if (!class_exists(\Laravel\Socialite\Facades\Socialite::class)) {
            \Log::error('Socialite facade not found when attempting Google redirect.');
            return redirect('/')->with('error', 'Socialite package is not installed. Run `composer require laravel/socialite` and restart the server.');
        }

        // Ensure Google OAuth config is present
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirect = config('services.google.redirect');

        if (empty($clientId) || empty($clientSecret)) {
            \Log::error('Google OAuth configuration missing', ['client_id' => (bool)$clientId, 'client_secret' => (bool)$clientSecret]);
            return redirect('/')->with('error', 'Google OAuth is not configured. Set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in your .env and restart the server.');
        }

        // Build the redirect URL from the incoming request host so the redirect_uri matches the caller
        $scheme = $request->getScheme();
        $host = $request->getHost();
        $port = $request->getPort();
        $includePort = $port && !in_array($port, [80, 443]);
        $computedRedirect = $scheme . '://' . $host . ($includePort ? ':' . $port : '') . '/auth/google/callback';

        // If user configured a specific redirect in services, prefer that; otherwise use computed
        $finalRedirect = !empty($redirect) ? $redirect : $computedRedirect;

            \Log::debug('Using Google redirect URI', ['final' => $finalRedirect, 'computed' => $computedRedirect, 'host' => $host, 'port' => $port]);

        return \Laravel\Socialite\Facades\Socialite::driver('google')
            ->stateless()
            ->redirectUrl($finalRedirect)
            ->redirect();
    }

    /**
     * Handle Google OAuth callback and login/create user.
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            if (!class_exists(\Laravel\Socialite\Facades\Socialite::class)) {
                \Log::error('Socialite facade not found during Google callback.');
                return redirect('/')->with('error', 'Socialite package is not installed. Run `composer require laravel/socialite` and restart the server.');
            }

            // Compute redirect URL the same way we did for the initial redirect so Google's response matches
            $scheme = $request->getScheme();
            $host = $request->getHost();
            $port = $request->getPort();
            $includePort = $port && !in_array($port, [80, 443]);
            $computedRedirect = $scheme . '://' . $host . ($includePort ? ':' . $port : '') . '/auth/google/callback';
            $configuredRedirect = config('services.google.redirect');
            $finalRedirect = !empty($configuredRedirect) ? $configuredRedirect : $computedRedirect;

            \Log::debug('Google callback using redirect URI', ['final' => $finalRedirect, 'computed' => $computedRedirect, 'host' => $host, 'port' => $port]);

            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')
                ->stateless()
                ->redirectUrl($finalRedirect)
                ->user();
            if (empty($googleUser->getEmail())) {
                return redirect('/login')->with('error', 'Google did not provide an email address.');
            }

            $query = User::query();

            if (Schema::hasColumn('users', 'provider_name') && Schema::hasColumn('users', 'provider_id')) {
                $query = User::where('provider_name', 'google')
                    ->where('provider_id', $googleUser->getId());
            }

            $existingUser = $query->first();

            if (!$existingUser) {
                $existingUser = User::where('email', $googleUser->getEmail())->first();
            }

            $request->session()->put('google_registration', [
                'provider_id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Student',
                'avatar' => $googleUser->getAvatar(),
                'email_verified' => true,
            ]);
            $request->session()->forget('google_registration_verified');

            if ($existingUser) {
                $existingUser->provider_name = 'google';
                $existingUser->provider_id = $googleUser->getId();
                $existingUser->provider_avatar = $googleUser->getAvatar();
                $existingUser->email_verified_at = $existingUser->email_verified_at ?: now();
                $existingUser->name = $existingUser->name ?: ($googleUser->getName() ?? $googleUser->getNickname() ?? 'Student');
                $existingUser->save();

                Auth::login($existingUser, true);
                return redirect('/dashboard');
            }

            return redirect('/')->with('google_registration_ready', true);
        } catch (\Exception $e) {
            \Log::error('Google auth callback error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Unable to sign in with Google. Please try again later.');
        }
    }

    /**
     * Send an OTP after Google account selection for the new Google-first onboarding flow.
     */
    public function sendGoogleOtp(Request $request)
    {
        try {
            $registration = $request->session()->get('google_registration');
            if (empty($registration['email'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google account selection is required before verification.',
                ], 422);
            }

            $email = strtolower($registration['email']);
            $name = trim($registration['name'] ?? '');
            $otp = (string) random_int(100000, 999999);
            $expiresAt = now()->addMinutes(10);

            // Rate limit: do not allow more than 3 requests in 10 minutes for the same email
            $recent = \App\Models\EmailOtp::where('email', $email)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->count();
            if ($recent >= 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many verification requests. Please try again later.',
                ], 429);
            }

            \App\Models\EmailOtp::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'otp_hash' => Hash::make($otp),
                    'attempt_count' => 0,
                    'expires_at' => $expiresAt,
                ]
            );

            // Send OTP via email
            try {
                Mail::raw(
                    "Your SHEELEARN verification code is: {$otp}\n\nThis code will expire in 10 minutes. Do not share this code with anyone.",
                    function ($message) use ($email, $name) {
                        $message->to($email);
                        $message->subject('Your SHEELEARN Verification Code');
                        $message->from(config('mail.from.address'), config('mail.from.name'));
                    }
                );
            } catch (\Exception $e) {
                \Log::error('Google OTP email send failed.', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to send verification email. Please try again later.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'We\'ve sent a 6-digit verification code to your Gmail address.',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Google OTP send error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to send verification code. Please try again later.',
            ], 500);
        }
    }

    /**
     * Verify the Google OTP and move the user into the profile-completion step.
     */
    public function verifyGoogleOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'otp' => ['required', 'digits:6'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification code format.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $registration = $request->session()->get('google_registration');
            $email = strtolower($registration['email'] ?? '');
            $emailOtp = $email ? \App\Models\EmailOtp::where('email', $email)->first() : null;

            if (!$emailOtp || !$emailOtp->otp_hash) {
                return response()->json([
                    'success' => false,
                    'message' => 'No verification code found. Please request a new one.',
                ], 422);
            }

            if ($emailOtp->expires_at->isPast()) {
                $emailOtp->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'Verification code has expired. Please request a new one.',
                ], 422);
            }

            if (!Hash::check($request->otp, $emailOtp->otp_hash)) {
                $emailOtp->attempt_count = ($emailOtp->attempt_count ?? 0) + 1;
                if ($emailOtp->attempt_count >= 5) {
                    $emailOtp->delete();
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many failed attempts. Please request a new verification code.',
                    ], 429);
                }
                $emailOtp->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect verification code. Please try again.',
                ], 422);
            }

            $emailOtp->delete();
            $request->session()->put('google_registration_verified', true);

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully.',
                'next_step' => 'profile',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Google OTP verify error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to verify your code. Please try again later.',
            ], 500);
        }
    }

    /**
     * Complete the Google-first registration flow by creating the account and logging the user in.
     */
    public function completeGoogleProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users,username'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $validator->after(function ($validator) use ($request) {
                $password = $request->password;
                if ($password && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $password)) {
                    $validator->errors()->add('password', 'Password must be at least 8 characters and include upper, lower, number, and special characters.');
                }
            });

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $registration = $request->session()->get('google_registration');
            $verified = $request->session()->get('google_registration_verified');
            if (empty($registration['email']) || !$verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please verify your email before creating an account.',
                ], 422);
            }

            $email = strtolower($registration['email']);
            $existingUser = User::where('email', $email)->first();

            return DB::transaction(function () use ($request, $registration, $email, $existingUser) {
                $hasUsernameColumn = Schema::hasColumn('users', 'username');
                $userData = [
                    'name' => $request->name,
                    'email' => $email,
                    'password' => Hash::make($request->password),
                    'plan' => config('ai_usage.default_plan', 'free'),
                    'provider_name' => 'google',
                    'provider_id' => $registration['provider_id'] ?? null,
                    'provider_avatar' => $registration['avatar'] ?? null,
                    'email_verified_at' => now(),
                    'settings' => [
                        'welcome_goal' => $request->goal ?? null,
                    ],
                ];

                if ($hasUsernameColumn) {
                    $userData['username'] = $request->username;
                }

                if ($existingUser) {
                    $existingUser->name = $request->name;
                    if ($hasUsernameColumn) {
                        $existingUser->username = $request->username;
                    }
                    $existingUser->email_verified_at = $existingUser->email_verified_at ?: now();
                    $existingUser->provider_name = $existingUser->provider_name ?: 'google';
                    $existingUser->provider_id = $existingUser->provider_id ?: ($registration['provider_id'] ?? null);
                    $existingUser->provider_avatar = $existingUser->provider_avatar ?: ($registration['avatar'] ?? null);
                    $existingUser->password = Hash::make($request->password);
                    $existingUser->save();
                    Auth::login($existingUser, true);

                    $request->session()->forget(['google_registration', 'google_registration_verified']);

                    return response()->json([
                        'success' => true,
                        'message' => 'Welcome back! Your account is ready.',
                        'user' => $existingUser,
                        'redirect' => '/dashboard',
                    ], 201);
                }

                $user = User::create($userData);

                Auth::login($user, true);
                $request->session()->forget(['google_registration', 'google_registration_verified']);

                return response()->json([
                    'success' => true,
                    'message' => 'Account created successfully.',
                    'user' => $user,
                    'redirect' => '/dashboard',
                ], 201);
            });
        } catch (\Exception $e) {
            \Log::error('Google profile completion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to finish registration. Please try again later.',
            ], 500);
        }
    }

    /**
     * Request a phone OTP to sign in or register.
     */
    public function requestPhoneOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => ['required', 'string', 'regex:/^\+?[0-9]{10,15}$/'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $phone = preg_replace('/[^0-9+]/', '', $request->phone);
            if ($phone && $phone[0] !== '+') {
                $phone = '+' . $phone;
            }
            $otp = (string) random_int(100000, 999999);
            $cacheKey = 'phone_otp:' . $phone;

            Cache::put($cacheKey, [
                'hash' => Hash::make($otp),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10)->timestamp,
            ], now()->addMinutes(10));

            $sentViaTwilio = false;
            $debugMode = config('app.debug') || app()->isLocal();

            if (config('services.twilio.sid') && config('services.twilio.token') && config('services.twilio.from')) {
                try {
                    $twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
                    $twilio->messages->create($phone, [
                        'from' => config('services.twilio.from'),
                        'body' => "Your SHEELEARN verification code is {$otp}. It expires in 10 minutes.",
                    ]);
                    $sentViaTwilio = true;
                } catch (\Exception $e) {
                    \Log::warning('Phone OTP send failed, falling back to log-only delivery.', [
                        'phone' => $phone,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                \Log::info('Phone OTP generated without Twilio send.', ['phone' => $phone, 'code' => $otp]);
            }

            $response = [
                'success' => true,
                'message' => $sentViaTwilio
                    ? 'Verification code has been sent. Please check your phone.'
                    : 'Verification code generated. No Twilio SMS send is configured, so the code is available in logs or debug output.',
            ];

            if (!$sentViaTwilio && $debugMode) {
                $response['debug_code'] = $otp;
            }

            return response()->json($response, 200);
        } catch (\Exception $e) {
            \Log::error('Phone OTP request error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to send phone verification code. Please try again later.'
            ], 500);
        }
    }

    public function verifyPhoneOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => ['required', 'string', 'regex:/^\+?[0-9]{10,15}$/'],
                'otp' => ['required', 'digits:6'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $phone = preg_replace('/[^0-9+]/', '', $request->phone);
            $cacheKey = 'phone_otp:' . $phone;
            $payload = Cache::get($cacheKey);

            if (empty($payload) || empty($payload['hash'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification code expired or invalid. Please request a new code.'
                ], 422);
            }

            if (!Hash::check($request->otp, $payload['hash'])) {
                $payload['attempts'] = ($payload['attempts'] ?? 0) + 1;
                if ($payload['attempts'] >= 5) {
                    Cache::forget($cacheKey);
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many failed attempts. Please request a new code.'
                    ], 429);
                }
                Cache::put($cacheKey, $payload, now()->addMinutes(10));

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification code. Please try again.'
                ], 422);
            }

            Cache::forget($cacheKey);

            $user = User::firstWhere('phone_number', $phone);
            if (!$user) {
                $user = User::create([
                    'name' => 'Learner',
                    'email' => 'phone+' . Str::slug($phone) . '@sheelearn.local',
                    'password' => Hash::make(Str::random(32)),
                    'plan' => config('ai_usage.default_plan', 'free'),
                    'phone_number' => $phone,
                    'phone_verified_at' => now(),
                    'provider_name' => 'phone_otp',
                ]);
            } else {
                $user->phone_verified_at = now();
                $user->provider_name = $user->provider_name ?: 'phone_otp';
                $user->save();
            }

            Auth::login($user, true);

            return response()->json([
                'success' => true,
                'message' => 'Phone verified. Signing you in now.',
                'user' => $user,
                'redirect' => '/dashboard'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Phone OTP verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to verify code. Please try again later.'
            ], 500);
        }
    }

    public function requestEmailOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'string', 'email', 'max:255'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = strtolower($request->email);
            $name = trim($request->name ?? '');
            $otp = (string) random_int(100000, 999999);
            $expiresAt = now()->addMinutes(5);

            // Rate limit: do not allow more than 3 requests in 10 minutes for the same email
            $recent = \App\Models\EmailOtp::where('email', $email)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->count();
            if ($recent >= 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many OTP requests. Please try again later.'
                ], 429);
            }

            $emailOtp = \App\Models\EmailOtp::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'otp_hash' => Hash::make($otp),
                    'attempt_count' => 0,
                    'expires_at' => $expiresAt,
                ]
            );

            $sentViaMail = false;
            $debugMode = config('app.debug') || app()->isLocal();

            if (config('mail.default')) {
                try {
                    Mail::raw("Your SHEELEARN verification code is {$otp}. It expires in 5 minutes.", function ($message) use ($email) {
                        $message->to($email);
                        $message->subject('Your SHEELEARN verification code');
                    });
                    $sentViaMail = true;
                } catch (\Exception $e) {
                    \Log::warning('Email OTP delivery failed.', ['email' => $email, 'error' => $e->getMessage()]);
                }
            } else {
                \Log::info('Email OTP generated without mail driver configured.', ['email' => $email, 'code' => $otp]);
            }

            $response = [
                'success' => true,
                'message' => $sentViaMail
                    ? 'Verification code has been sent. Please check your inbox.'
                    : 'Verification code generated. No mail delivery is configured, so the code is available in logs or debug output.',
            ];

            if (!$sentViaMail && $debugMode) {
                $response['debug_code'] = $otp;
            }

            return response()->json($response, 200);
        } catch (\Exception $e) {
            \Log::error('Email OTP request error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to send email verification code. Please try again later.'
            ], 500);
        }
    }

    /**
     * Request a password-reset OTP sent to the user's email.
     */
    public function requestPasswordReset(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'string', 'email', 'max:255'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = strtolower($request->email);

            $user = User::where('email', $email)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No SHEELEARN account was found with this email address.'
                ], 404);
            }

            $otp = (string) random_int(100000, 999999);
            $expiresAt = now()->addMinutes(10);

            // Prevent too-frequent resends: at least 60s between sends
            $existing = \App\Models\EmailOtp::where('email', $email)->first();
            if ($existing && $existing->created_at && $existing->created_at->gt(now()->subSeconds(60))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please wait before requesting a new code.'
                ], 429);
            }

            // Rate limit overall recent requests
            $recent = \App\Models\EmailOtp::where('email', $email)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->count();
            if ($recent >= 6) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many verification requests. Please try again later.'
                ], 429);
            }

            \App\Models\EmailOtp::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $user->name ?? null,
                    'otp_hash' => Hash::make($otp),
                    'attempt_count' => 0,
                    'expires_at' => $expiresAt,
                ]
            );

            try {
                Mail::raw("Your SHEELEARN password reset verification code is: {$otp}\n\nThis code will expire in 10 minutes.", function ($message) use ($email) {
                    $message->to($email);
                    $message->subject('SHEELEARN Password Reset Code');
                });
            } catch (\Exception $e) {
                \Log::error('Password reset OTP email send failed.', ['email' => $email, 'error' => $e->getMessage()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to send verification email. Please try again later.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'We sent a 6-digit verification code if the account exists.'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Password reset request error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to send verification code. Please try again later.'
            ], 500);
        }
    }

    /**
     * Verify password-reset OTP and allow proceeding to password change.
     */
    public function verifyPasswordReset(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'string', 'email', 'max:255'],
                'otp' => ['required', 'digits:6'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = strtolower($request->email);
            $emailOtp = \App\Models\EmailOtp::where('email', $email)->first();

            if (!$emailOtp || !$emailOtp->otp_hash || $emailOtp->expires_at->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification code expired or invalid. Please request a new code.'
                ], 422);
            }

            if (!Hash::check($request->otp, $emailOtp->otp_hash)) {
                $emailOtp->attempt_count = ($emailOtp->attempt_count ?? 0) + 1;
                if ($emailOtp->attempt_count >= 5) {
                    $emailOtp->delete();
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many failed attempts. Please request a new verification code.'
                    ], 429);
                }
                $emailOtp->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect verification code. Please try again.'
                ], 422);
            }

            // Valid: consume OTP and mark session for password reset
            $emailOtp->delete();
            $request->session()->put('password_reset_email', $email);
            $request->session()->put('password_reset_verified', true);

            return response()->json([
                'success' => true,
                'message' => 'Email verified. You may now set a new password.',
                'redirect' => '/password/reset'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Password reset verify error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to verify your code. Please try again later.'
            ], 500);
        }
    }

    /**
     * Complete password reset by setting a new password.
     */
    public function completePasswordReset(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $validator->after(function ($validator) use ($request) {
                $password = $request->password;
                if ($password && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
                    $validator->errors()->add('password', 'Password must be at least 8 characters and include upper, lower, and number.');
                }
            });

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $email = $request->session()->get('password_reset_email');
            $verified = $request->session()->get('password_reset_verified');
            if (!$email || !$verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password reset verification required.'
                ], 422);
            }

            $user = User::where('email', $email)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No user found for this password reset request.'
                ], 404);
            }

            if (Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'New password cannot be the same as your previous password.'
                ], 422);
            }

            $user->password = Hash::make($request->password);
            $user->email_verified_at = $user->email_verified_at ?: now();
            $user->save();

            // Clear session flags and log user in
            $request->session()->forget(['password_reset_email', 'password_reset_verified']);
            Auth::login($user, true);

            return response()->json([
                'success' => true,
                'message' => 'Your password has been updated successfully.',
                'redirect' => '/dashboard'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Password reset complete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to update password. Please try again later.'
            ], 500);
        }
    }

    public function verifyEmailOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'string', 'email', 'max:255'],
                'otp' => ['required', 'digits:6'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = strtolower($request->email);
            $emailOtp = \App\Models\EmailOtp::where('email', $email)->first();

            if (!$emailOtp || !$emailOtp->otp_hash || $emailOtp->expires_at->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification code expired or invalid. Please request a new code.'
                ], 422);
            }

            if (!Hash::check($request->otp, $emailOtp->otp_hash)) {
                $emailOtp->attempt_count = ($emailOtp->attempt_count ?? 0) + 1;
                if ($emailOtp->attempt_count >= 5) {
                    $emailOtp->delete();
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many failed attempts. Please request a new code.'
                    ], 429);
                }
                $emailOtp->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification code. Please try again.'
                ], 422);
            }

            // capture name and consume record
            $requestedName = $emailOtp->name ?? null;
            $emailOtp->delete();

            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $requestedName ?: ucfirst(explode('@', $email)[0]),
                    'email' => $email,
                    'password' => Hash::make(Str::random(32)),
                    'plan' => config('ai_usage.default_plan', 'free'),
                    'provider_name' => 'email_otp',
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->email_verified_at = now();
                $user->provider_name = $user->provider_name ?: 'email_otp';
                $user->save();
            }

            Auth::login($user, true);

            return response()->json([
                'success' => true,
                'message' => 'Email verified. Signing you in now.',
                'user' => $user,
                'redirect' => '/dashboard'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Email OTP verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to verify code. Please try again later.'
            ], 500);
        }
    }

    /**
     * Verify Firebase ID token sent from client after phone auth.
     * Expects JSON: { id_token: string }
     */
    public function verifyFirebaseToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_token' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $idToken = $request->id_token;

        if (!class_exists(\Kreait\Firebase\Auth::class)) {
            return response()->json(['success' => false, 'message' => 'Server Firebase verification not configured. Install kreait/firebase-admin and set FIREBASE_CREDENTIALS.'], 500);
        }

        try {
            $factory = (new \Kreait\Firebase\Factory())->withServiceAccount(config('services.firebase.credentials') ?: env('FIREBASE_CREDENTIALS'));
            $auth = $factory->createAuth();

            $verified = $auth->verifyIdToken($idToken);
            $claims = $verified->claims();
            $uid = $claims->get('sub');
            $phone = $claims->get('phone_number') ?? null;
            $email = $claims->get('email') ?? null;

            if (!$uid) {
                return response()->json(['success' => false, 'message' => 'Invalid Firebase token.'], 422);
            }

            // Normalize phone
            if ($phone && $phone[0] !== '+') {
                $phone = '+' . preg_replace('/[^0-9]/', '', $phone);
            }

            $user = null;
            if ($uid) {
                $user = User::firstWhere('firebase_uid', $uid);
            }
            if (!$user && $phone) {
                $user = User::firstWhere('phone_number', $phone);
            }

            if (!$user) {
                $user = User::create([
                    'name' => 'Learner',
                    'email' => $email ?: ('phone+' . Str::slug($uid) . '@sheelearn.local'),
                    'password' => Hash::make(Str::random(32)),
                    'plan' => config('ai_usage.default_plan', 'free'),
                    'phone_number' => $phone,
                    'phone_verified_at' => now(),
                    'firebase_uid' => $uid,
                    'login_provider' => 'phone',
                ]);
            } else {
                $user->firebase_uid = $user->firebase_uid ?: $uid;
                $user->phone_number = $user->phone_number ?: $phone;
                $user->phone_verified_at = $user->phone_verified_at ?: now();
                $user->login_provider = 'phone';
                $user->save();
            }

            Auth::login($user, true);

            return response()->json(['success' => true, 'message' => 'Phone verified. Signing you in now.', 'redirect' => '/dashboard', 'user' => $user], 200);
        } catch (\Kreait\Firebase\Exception\AuthException $e) {
            \Log::warning('Firebase token verification failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Invalid or expired Firebase token.'], 422);
        } catch (\Exception $e) {
            \Log::error('Firebase verification error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to verify phone at this time.'], 500);
        }
    }

    /**
     * API Logout - Handle user logout via modal
     * Always redirects to welcome page to provide seamless user experience
     */
    public function apiLogout(Request $request)
    {
        try {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Always redirect to welcome page instead of returning JSON
            return redirect('/')->with('message', 'Logged out successfully!');
        } catch (\Exception $e) {
            \Log::error('Logout error: ' . $e->getMessage());
            return redirect('/')->with('error', 'An error occurred during logout');
        }
    }

    /**
     * API Update Profile - Change name and email
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id),
                ],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user->name = $request->name;
            if ($user->email !== $request->email) {
                $user->email = $request->email;
                $user->email_verified_at = null;
            }
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Profile update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating your profile.'
            ], 500);
        }
    }

    /**
     * API Update Password - Change authenticated user's password
     */
    public function updatePassword(Request $request)
    {
        try {
            $user = Auth::user();
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The current password is incorrect.'
                ], 403);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully.'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Password change error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while changing your password.'
            ], 500);
        }
    }

    /**
     * API Upload Avatar - Persist user profile photo to storage and settings
     */
    public function uploadAvatar(Request $request)
    {
        try {
            $user = Auth::user();
            \Log::info('Avatar upload attempt for user ' . $user->id, [
                'has_file' => $request->hasFile('avatar'),
                'file_size' => $request->file('avatar') ? $request->file('avatar')->getSize() : null,
            ]);

            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,png,gif,webp|max:2048',
            ]);

            if ($validator->fails()) {
                \Log::warning('Avatar validation failed', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image format or size. Use JPEG, PNG, GIF, or WebP (max 2MB).',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('avatar');
            $path = $file->store('avatars', 'public');
            $storedUrl = Storage::disk('public')->url($path); // may be relative or absolute
            $pathOnly = parse_url($storedUrl, PHP_URL_PATH) ?: '/storage/' . ltrim($path, '/');
            $avatarUrl = $request->getSchemeAndHttpHost() . $pathOnly; // ensure returned URL uses current request host (includes port)
            \Log::info('Avatar stored successfully', ['path' => $path, 'storedUrl' => $storedUrl, 'avatarUrl' => $avatarUrl]);

            $settings = is_array($user->settings)
                ? $user->settings
                : (json_decode($user->settings ?? '[]', true) ?: []);

            if (!empty($settings['profile_avatar'])) {
                $existingPath = str_replace('/storage/', '', parse_url($settings['profile_avatar'], PHP_URL_PATH));
                if ($existingPath && Storage::disk('public')->exists($existingPath)) {
                    Storage::disk('public')->delete($existingPath);
                }
            }

            $settings['profile_avatar'] = $avatarUrl;
            $user->settings = $settings;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile photo uploaded successfully.',
                'avatar_url' => $avatarUrl,
                'url' => $avatarUrl,
                'settings' => $user->settings
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Avatar upload error: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     */
    public function getUser(Request $request)
    {
        try {
            return response()->json([
                'success' => true,
                'user' => $request->user()
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Get user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching user'
            ], 500);
        }
    }

    /**
     * Get current user's avatar URL
     */
    public function getAvatar(Request $request)
    {
        try {
            $user = $request->user();
            $avatarUrl = $user->settings['profile_avatar'] ?? null;
            // Normalize stored avatar host to current request host if it's a local storage path
            if ($avatarUrl) {
                $pathOnly = parse_url($avatarUrl, PHP_URL_PATH) ?: null;
                if ($pathOnly) {
                    $avatarUrl = $request->getSchemeAndHttpHost() . $pathOnly;
                }
            }
            
            return response()->json([
                'success' => true,
                'avatar_url' => $avatarUrl,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Get avatar error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching avatar'
            ], 500);
        }
    }
}

