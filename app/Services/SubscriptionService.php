<?php

namespace App\Services;

use App\Models\User;

class SubscriptionService
{
    public const PLAN_GUEST = 'guest';
    public const PLAN_FREE = 'free';
    public const PLAN_PREMIUM = 'premium';

    protected array $plans;

    public function __construct()
    {
        $this->plans = config('ai_usage.plans', []);
    }

    public function getPlan(User $user): string
    {
        $plan = $user->getRawOriginal('plan') ?? config('ai_usage.default_plan', self::PLAN_FREE);
        $plan = strtolower((string) $plan);

        if ($plan === self::PLAN_PREMIUM) {
            return self::PLAN_PREMIUM;
        }

        if ($plan === self::PLAN_GUEST) {
            return self::PLAN_GUEST;
        }

        return self::PLAN_FREE;
    }

    public function getPlanLabel(string $plan): string
    {
        return $this->plans[$plan]['label'] ?? ucfirst($plan);
    }

    public function getLimit(string $plan): ?int
    {
        return $this->plans[$plan]['limit'] ?? null;
    }

    public function getResetIntervalHours(string $plan): ?int
    {
        return $this->plans[$plan]['reset_interval_hours'] ?? config('ai_usage.reset_interval_hours', 24);
    }

    public function isUnlimited(string $plan): bool
    {
        return is_null($this->getLimit($plan));
    }
}
