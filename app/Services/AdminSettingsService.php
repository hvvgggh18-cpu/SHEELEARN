<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class AdminSettingsService
{
    private const STORAGE_FILE = 'app/admin_settings.json';

    public function loadSettings(): array
    {
        $defaults = config('admin', []);

        if (! File::exists(storage_path(self::STORAGE_FILE))) {
            return $defaults;
        }

        $raw = File::get(storage_path(self::STORAGE_FILE));
        $stored = json_decode($raw, true);

        if (! is_array($stored)) {
            return $defaults;
        }

        return array_merge($defaults, $stored);
    }

    public function saveSettings(array $settings): array
    {
        $current = $this->loadSettings();
        $merged = array_merge($current, $settings);

        File::ensureDirectoryExists(storage_path('app'));
        File::put(storage_path(self::STORAGE_FILE), json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $merged;
    }
}
