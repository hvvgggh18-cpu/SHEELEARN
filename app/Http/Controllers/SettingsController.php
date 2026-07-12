<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $profileAvatar = data_get($user->settings, 'profile_avatar');
        $school = data_get($user->settings, 'school');

        $storageBytes = $user->documents()
            ->where('status', '!=', 'removed')
            ->sum('size');

        $storageLimitBytes = 500 * 1024 * 1024;
        $storageUsagePercent = $storageLimitBytes > 0
            ? (int) min(100, round(($storageBytes / $storageLimitBytes) * 100))
            : 0;

        return view('settings', [
            'user' => $user,
            'profileAvatar' => $profileAvatar,
            'school' => $school,
            'storageUsedText' => $this->formatBytes($storageBytes),
            'storageUsagePercent' => $storageUsagePercent,
            'storageLimitText' => $this->formatBytes($storageLimitBytes),
        ]);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024 * 1024) {
            return number_format($bytes / (1024 * 1024 * 1024), 1) . ' GB';
        }

        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 0) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }

        return $bytes . ' bytes';
    }
}
