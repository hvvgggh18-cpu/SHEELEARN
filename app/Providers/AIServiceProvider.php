<?php

namespace App\Providers;

use App\Services\AIServiceInterface;
use App\Services\GroqService;
use Illuminate\Support\ServiceProvider;

class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AIServiceInterface::class, GroqService::class);
    }
}
