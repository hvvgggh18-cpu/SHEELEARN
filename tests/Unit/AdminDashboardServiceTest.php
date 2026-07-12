<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_trend_uses_real_user_creation_counts(): void
    {
        $service = app(AdminDashboardService::class);
        $today = now()->startOfDay();

        User::factory()->create(['created_at' => $today->copy()->subDays(0)]);
        User::factory()->create(['created_at' => $today->copy()->subDays(2)]);
        User::factory()->create(['created_at' => $today->copy()->subDays(6)]);

        $trend = $service->getRegistrationTrend(7);

        $this->assertCount(7, $trend['labels']);
        $this->assertCount(7, $trend['values']);
        $this->assertSame(3, array_sum($trend['values']));
    }
}
