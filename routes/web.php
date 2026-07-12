<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AIChatController;
use App\Http\Controllers\FlashcardsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudyPlannerController;
use App\Http\Middleware\EnsureAdminAuthenticated;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\NotificationController;

Route::get('/', [WelcomeController::class, 'index']);

Route::get('/login', [WelcomeController::class, 'index'])->name('login');

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');

    Route::middleware([EnsureAdminAuthenticated::class])->group(function () {
        Route::get('/', [AdminAuthController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/dashboard', [AdminAuthController::class, 'dashboard'])->name('admin.dashboard.home');
        Route::get('/users', [AdminAuthController::class, 'users'])->name('admin.users');
        Route::post('/users/{user}/toggle-status', [AdminAuthController::class, 'toggleUserStatus'])->name('admin.users.toggle-status');
        Route::get('/ai-usage', [AdminAuthController::class, 'aiUsage'])->name('admin.ai-usage');
        Route::get('/learning-content', [AdminAuthController::class, 'learningContent'])->name('admin.learning-content');
        Route::get('/flashcards', [AdminAuthController::class, 'flashcards'])->name('admin.flashcards');
        Route::get('/quizzes', [AdminAuthController::class, 'quizzes'])->name('admin.quizzes');
        Route::get('/documents', [AdminAuthController::class, 'documents'])->name('admin.documents');
        Route::get('/announcements', [AdminAuthController::class, 'announcements'])->name('admin.announcements');
        Route::get('/reports', [AdminAuthController::class, 'reports'])->name('admin.reports');
        Route::get('/profile', [AdminAuthController::class, 'profile'])->name('admin.profile');
        Route::patch('/profile', [AdminAuthController::class, 'updateProfile'])->name('admin.profile.update');
        Route::patch('/password', [AdminAuthController::class, 'updatePassword'])->name('admin.password.update');
        Route::get('/settings', [AdminAuthController::class, 'settings'])->name('admin.settings');
        Route::patch('/settings', [AdminAuthController::class, 'updateSettings'])->name('admin.settings.update');
        Route::get('/logs', [AdminAuthController::class, 'logs'])->name('admin.logs');
        Route::get('/analytics', [AdminAuthController::class, 'analytics'])->name('admin.analytics');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    });
});

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// API Authentication routes (for modal-based auth)
// These are excluded from CSRF verification in app/Http/Middleware/VerifyCsrfToken.php
Route::prefix('api/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'apiRegister']);
    Route::post('/login', [AuthController::class, 'apiLogin']);
    Route::post('/logout', [AuthController::class, 'apiLogout'])->middleware('auth')->name('logout');
    Route::post('/otp/request', [AuthController::class, 'requestPhoneOtp']);
    Route::post('/otp/verify', [AuthController::class, 'verifyPhoneOtp']);
    Route::post('/email-otp/request', [AuthController::class, 'requestEmailOtp']);
    Route::post('/email-otp/verify', [AuthController::class, 'verifyEmailOtp']);
    Route::post('/google/otp/send', [AuthController::class, 'sendGoogleOtp']);
    Route::post('/google/otp/verify', [AuthController::class, 'verifyGoogleOtp']);
    Route::post('/google/profile/complete', [AuthController::class, 'completeGoogleProfile']);
    Route::post('/password-reset/request', [AuthController::class, 'requestPasswordReset']);
    Route::post('/password-reset/verify', [AuthController::class, 'verifyPasswordReset']);
    Route::post('/password-reset/complete', [AuthController::class, 'completePasswordReset']);
    Route::post('/gmail/start', [AuthController::class, 'startGmailRegistration']);
    Route::post('/gmail/verify', [AuthController::class, 'verifyGmailRegistration']);
    Route::post('/gmail/complete', [AuthController::class, 'completeGmailRegistration']);
    Route::post('/phone/firebase-verify', [AuthController::class, 'verifyFirebaseToken']);
    Route::get('/user', [AuthController::class, 'getUser'])->middleware('auth');
    Route::get('/user/avatar', [AuthController::class, 'getAvatar'])->middleware('auth')->name('user.avatar');
    Route::patch('/account', [AuthController::class, 'updateProfile'])->middleware('auth')->name('account.update');

    Route::patch('/account/password', [AuthController::class, 'updatePassword'])->middleware('auth')->name('account.password.update');
    Route::post('/account/avatar', [AuthController::class, 'uploadAvatar'])->middleware('auth')->name('account.avatar.upload');
    Route::delete('/account', [AuthController::class, 'deleteAccount'])->middleware('auth')->name('account.delete');

});

// Public API routes for welcome page
Route::prefix('api')->group(function () {
    Route::get('/welcome/statistics', [WelcomeController::class, 'getStatistics'])->name('api.welcome.statistics');
    
    // Debug route - remove in production
    Route::get('/debug/users-count', function () {
        return response()->json([
            'total_users' => \App\Models\User::count(),
            'total_documents' => \App\Models\Document::count(),
            'help_feedback_count' => \App\Models\HelpFeedback::count(),
            'average_rating' => \App\Models\HelpFeedback::avg('rating'),
        ]);
    })->name('api.debug.users');
});

// Password reset pages (email OTP flow)
Route::get('/password/forgot', function () { return view('auth.forgot_password'); })->name('password.forgot');
Route::get('/password/verify', function () { return view('auth.verify_otp'); })->name('password.verify');
Route::get('/password/reset', function () { return view('auth.reset_password'); })->name('password.reset');

// Dashboard route (protected)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        // Temporarily increase max execution time to diagnose long-running render issues
        @ini_set('max_execution_time', '120');
        return view('dashboard');
    })->name('dashboard');

    // Dashboard stats API (returns JSON for live updates)
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    // Seed demo data for the authenticated user (dev only)
    Route::get('/dashboard/seed-demo', [DashboardController::class, 'seedDemo'])->name('dashboard.seed-demo');

    // Main menu routes
    Route::get('/ai-chat', [AIChatController::class, 'index'])->name('ai-chat');
    Route::post('/ai-chat/upload-document', [App\Http\Controllers\DocumentController::class, 'upload'])->name('ai-chat.upload');
    Route::get('/ai-chat/documents', [App\Http\Controllers\DocumentController::class, 'list'])->name('ai-chat.documents');
    Route::get('/ai-chat/documents/{document}', [App\Http\Controllers\DocumentController::class, 'show'])->name('ai-chat.documents.show');
    Route::post('/ai-chat/documents/{document}/process', [App\Http\Controllers\DocumentController::class, 'process'])->name('ai-chat.documents.process');
    Route::get('/ai-chat/documents/{document}/download', [App\Http\Controllers\DocumentController::class, 'download'])->name('ai-chat.documents.download');
    Route::get('/ai-chat/conversation/{conversation}', [AIChatController::class, 'conversation'])->name('ai-chat.conversation');
    Route::delete('/ai-chat/conversation/{conversation}', [AIChatController::class, 'deleteConversation'])->name('ai-chat.conversation.delete');
    Route::post('/ai-chat/message', [AIChatController::class, 'send'])->name('ai-chat.send');
    Route::delete('/ai-chat/history', [AIChatController::class, 'clearHistory'])->name('ai-chat.clear-history');
    Route::post('/ai-chat/attachment/clear', [AIChatController::class, 'clearAttachment'])->name('ai-chat.clear-attachment');

    Route::get('/summarizer', function () {
        return view('summarizer');
    })->name('summarizer');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    // Profile avatar upload route (used by settings view JS)
    Route::post('/settings/avatar', [AuthController::class, 'uploadAvatar'])->name('settings.avatar');

    Route::get('/flashcards', [FlashcardsController::class, 'index'])->name('flashcards');
    Route::get('/flashcards/decks', [FlashcardsController::class, 'listDecks'])->name('flashcards.decks');
    Route::get('/flashcards/decks/{deck}', [FlashcardsController::class, 'showDeck'])->name('flashcards.decks.show');
    Route::post('/flashcards/upload-document', [FlashcardsController::class, 'uploadDocument'])->name('flashcards.upload');
    Route::post('/flashcards/generate', [FlashcardsController::class, 'generate'])->name('flashcards.generate');
    Route::patch('/flashcards/cards/{flashcard}', [FlashcardsController::class, 'updateFlashcard'])->name('flashcards.cards.update');
    Route::delete('/flashcards/cards/{flashcard}', [FlashcardsController::class, 'deleteFlashcard'])->name('flashcards.cards.delete');
    Route::delete('/flashcards/decks/{deck}', [FlashcardsController::class, 'deleteDeck'])->name('flashcards.decks.delete');
    Route::post('/flashcards/interaction', [FlashcardsController::class, 'recordInteraction'])->name('flashcards.interaction');
    Route::get('/flashcards/analytics/{deck?}', [FlashcardsController::class, 'analytics'])->name('flashcards.analytics');
    Route::post('/flashcards/decks/{deck}/reset', [FlashcardsController::class, 'resetDeckProgress'])->name('flashcards.decks.reset');

    Route::get('/quizzes', function () {
        return view('quizzes');
    })->name('quizzes');

    Route::get('/documents', function () {
        return view('documents');
    })->name('documents');

    // Tools menu routes
    Route::get('/study-planner', [StudyPlannerController::class, 'index'])->name('study-planner');
    Route::get('/study-planner/state', [StudyPlannerController::class, 'state'])->name('study-planner.state');
    Route::post('/study-planner/tasks', [StudyPlannerController::class, 'storeTask'])->name('study-planner.tasks.store');
    Route::post('/study-planner/tasks/{task}/complete', [StudyPlannerController::class, 'completeTask'])->name('study-planner.tasks.complete');
    Route::post('/study-planner/schedule/generate', [StudyPlannerController::class, 'generateSchedule'])->name('study-planner.schedule.generate');

    Route::get('/analytics', function () {
        return view('analytics');
    })->name('analytics');

    Route::get('/analytics/stats', [DashboardController::class, 'analyticsStats'])->name('analytics.stats');

    Route::get('/notes', function () {
        return view('notes');
    })->name('notes');

    // Notifications API (simple JSON endpoints for navbar)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');

    // Account menu routes
    Route::get('/help', [App\Http\Controllers\HelpSupportController::class, 'index'])->name('help');
    Route::get('/help/search', [App\Http\Controllers\HelpSupportController::class, 'search'])->name('help.search');
    Route::post('/help/ticket', [App\Http\Controllers\HelpSupportController::class, 'submitTicket'])->name('help.ticket.submit');
    Route::post('/help/bug', [App\Http\Controllers\HelpSupportController::class, 'submitBug'])->name('help.bug.submit');
    Route::post('/help/feature', [App\Http\Controllers\HelpSupportController::class, 'submitFeature'])->name('help.feature.submit');
    Route::post('/help/feedback', [App\Http\Controllers\HelpSupportController::class, 'submitFeedback'])->name('help.feedback.submit');
    Route::get('/help/status', [App\Http\Controllers\HelpSupportController::class, 'systemStatus'])->name('help.status');
});
