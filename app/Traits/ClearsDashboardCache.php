<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait ClearsDashboardCache
{
    protected static function bootClearsDashboardCache()
    {
        static::saved(function ($model) {
            $userId = $model->user_id ?? null;
            if ($userId) {
                Cache::forget("dashboard_stats:user:{$userId}");
            }
        });

        static::deleted(function ($model) {
            $userId = $model->user_id ?? null;
            if ($userId) {
                Cache::forget("dashboard_stats:user:{$userId}");
            }
        });
    }
}
