<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AdminActivityLog;
use App\Models\User;
use App\Services\AdminDashboardService;
use App\Services\AdminSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function __construct(
        private readonly AdminDashboardService $dashboardService,
        private readonly AdminSettingsService $settingsService
    ) {
    }

    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard.home');
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = 'admin-login:' . $request->ip() . ':' . $request->userAgent();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => 'Too many attempts. Please try again in ' . $seconds . ' seconds.',
            ]);
        }

        RateLimiter::hit($throttleKey, 60);

        $admin = Admin::where('email', strtolower($request->input('email')))->first();

        if (! $admin || ! Hash::check($request->input('password'), $admin->password)) {
            $this->logActivity($admin?->id, 'failed_login', 'Invalid administrator credentials', $request);

            return back()->withErrors([
                'email' => 'Invalid administrator credentials.',
            ])->onlyInput('email');
        }

        if ($admin->status !== 'active') {
            $this->logActivity($admin->id, 'failed_login', 'Inactive administrator account', $request);

            return back()->withErrors([
                'email' => 'Invalid administrator credentials.',
            ])->onlyInput('email');
        }

        Auth::guard('admin')->login($admin, $request->boolean('remember'));
        $request->session()->regenerate();
        RateLimiter::clear($throttleKey);

        $this->logActivity($admin->id, 'successful_login', 'Administrator signed in successfully', $request);

        return redirect()->route('admin.dashboard.home');
    }

    public function dashboard()
    {
        return view('admin.dashboard', ['admin' => Auth::guard('admin')->user()]);
    }

    public function users()
    {
        $users = User::latest()->get();

        return view('admin.users', [
            'admin' => Auth::guard('admin')->user(),
            'users' => $users,
        ]);
    }

    public function toggleUserStatus(User $user)
    {
        $user->status = $user->status === 'active' ? 'suspended' : 'active';
        $user->save();

        return redirect()->route('admin.users')->with('success', 'User status updated.');
    }

    public function aiUsage()
    {
        return view('admin.ai-usage', [
            'admin' => Auth::guard('admin')->user(),
            'aiUsage' => $this->dashboardService->getAiUsageData(),
        ]);
    }

    public function learningContent()
    {
        return view('admin.learning-content', [
            'admin' => Auth::guard('admin')->user(),
            'content' => $this->dashboardService->getLearningContentData(),
        ]);
    }

    public function flashcards()
    {
        return view('admin.flashcards', [
            'admin' => Auth::guard('admin')->user(),
            'flashcards' => $this->dashboardService->getFlashcardsData(),
        ]);
    }

    public function quizzes()
    {
        return view('admin.quizzes', [
            'admin' => Auth::guard('admin')->user(),
            'quizzes' => $this->dashboardService->getQuizzesData(),
        ]);
    }

    public function documents()
    {
        return view('admin.documents', [
            'admin' => Auth::guard('admin')->user(),
            'documents' => $this->dashboardService->getDocumentsData(),
        ]);
    }

    public function announcements()
    {
        return view('admin.announcements', [
            'admin' => Auth::guard('admin')->user(),
            'announcements' => $this->dashboardService->getAnnouncementsData(),
        ]);
    }

    public function reports()
    {
        return view('admin.reports', [
            'admin' => Auth::guard('admin')->user(),
            'reports' => $this->dashboardService->getReportsData(),
        ]);
    }

    public function profile()
    {
        return view('admin.profile', ['admin' => Auth::guard('admin')->user()]);
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return response()->json(['success' => false, 'message' => 'Administrator session expired.'], 401);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email,' . $admin->id],
        ]);

        $admin->forceFill([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
        ])->save();

        return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
    }

    public function updatePassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return response()->json(['success' => false, 'message' => 'Administrator session expired.'], 401);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $admin->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
        }

        $admin->forceFill([
            'password' => Hash::make($validated['new_password']),
        ])->save();

        return response()->json(['success' => true, 'message' => 'Password updated successfully.']);
    }

    public function settings()
    {
        return view('admin.settings', [
            'admin' => Auth::guard('admin')->user(),
            'settings' => $this->settingsService->loadSettings(),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'platform_name' => ['sometimes', 'required', 'string', 'max:255'],
            'support_email' => ['sometimes', 'required', 'email', 'max:255'],
            'default_model' => ['sometimes', 'required', 'string', 'max:100'],
            'max_tokens' => ['sometimes', 'required', 'integer', 'min:1'],
            'temperature' => ['sometimes', 'required', 'numeric', 'between:0,2'],
            'maintenance_mode' => ['sometimes', 'boolean'],
            'allow_registrations' => ['sometimes', 'boolean'],
            'two_factor_auth' => ['sometimes', 'boolean'],
            'session_timeout' => ['sometimes', 'boolean'],
            'session_duration' => ['sometimes', 'required', 'integer', 'min:1'],
            'max_login_attempts' => ['sometimes', 'required', 'integer', 'min:1'],
        ]);

        $saved = $this->settingsService->saveSettings($validated);

        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully.',
            'settings' => $saved,
        ]);
    }

    public function logs()
    {
        return view('admin.logs', [
            'admin' => Auth::guard('admin')->user(),
            'logs' => $this->dashboardService->getLogsData(),
        ]);
    }

    public function analytics()
    {
        return view('admin.analytics', [
            'admin' => Auth::guard('admin')->user(),
            'analytics' => $this->dashboardService->getAnalyticsData(),
        ]);
    }

    public function logout(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($admin) {
            $this->logActivity($admin->id, 'logout', 'Administrator signed out', $request);
        }

        return redirect('/admin/login');
    }

    private function logActivity(?int $adminId, string $action, string $details, Request $request): void
    {
        AdminActivityLog::create([
            'admin_id' => $adminId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
