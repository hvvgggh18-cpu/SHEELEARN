<?php

namespace App\Services;

use App\Exceptions\QuotaExceededException;
use App\Models\AiUsage;
use App\Models\User;
use Carbon\Carbon;

class AIUsageLimiterService
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function getUsageSummary(User $user): array
    {
        $usage = $this->getUsageRecord($user);
        $this->refreshIfExpired($usage);

        return $this->normalizeUsage($usage);
    }

    public function ensureCanUse(User $user): void
    {
        $usage = $this->getUsageRecord($user);
        $this->refreshIfExpired($usage);

        if ($usage->isUnlimited()) {
            return;
        }

        if ($usage->used >= $usage->allowed) {
            throw new QuotaExceededException(
                sprintf('You have reached your free AI limit. Your quota will reset in %s, or upgrade to Premium for unlimited access.',
                    $usage->next_reset_at ? $usage->next_reset_at->diffForHumans(null, true) : 'some time'),
                $usage->plan,
                $usage->used,
                $usage->allowed,
                $usage->next_reset_at,
            );
        }
    }

    public function incrementUsage(User $user): void
    {
        $usage = $this->getUsageRecord($user);
        $this->refreshIfExpired($usage);

        if ($usage->isUnlimited()) {
            return;
        }

        $usage->increment('used');
    }

    protected function getUsageRecord(User $user): AiUsage
    {
        $plan = $this->subscriptionService->getPlan($user);
        $allowed = $this->subscriptionService->getLimit($plan);
        $intervalHours = $this->subscriptionService->getResetIntervalHours($plan);

        $usage = AiUsage::firstOrCreate(
            ['user_id' => $user->id],
            [
                'plan' => $plan,
                'used' => 0,
                'allowed' => $allowed,
                'reset_interval_hours' => $intervalHours,
                'last_reset_at' => $this->now(),
                'next_reset_at' => $this->calculateNextResetAt($plan),
            ]
        );

        if ($usage->plan !== $plan || $usage->allowed !== $allowed || $usage->reset_interval_hours !== $intervalHours) {
            $usage->plan = $plan;
            $usage->allowed = $allowed;
            $usage->reset_interval_hours = $intervalHours;
            $usage->save();
        }

        return $usage;
    }

    protected function refreshIfExpired(AiUsage $usage): void
    {
        if ($usage->isUnlimited() || $usage->next_reset_at === null) {
            return;
        }

        if ($usage->next_reset_at->isPast()) {
            $this->resetUsage($usage);
        }
    }

    protected function resetUsage(AiUsage $usage): void
    {
        $plan = $usage->plan;
        $usage->used = 0;
        $usage->allowed = $this->subscriptionService->getLimit($plan);
        $usage->reset_interval_hours = $this->subscriptionService->getResetIntervalHours($plan);
        $usage->last_reset_at = $this->now();
        $usage->next_reset_at = $this->calculateNextResetAt($plan);
        $usage->save();
    }

    protected function calculateNextResetAt(string $plan): ?Carbon
    {
        if ($this->subscriptionService->isUnlimited($plan)) {
            return null;
        }

        $intervalHours = $this->subscriptionService->getResetIntervalHours($plan);

        if ($intervalHours === null) {
            return null;
        }

        return $this->now()->addHours($intervalHours);
    }

    protected function normalizeUsage(AiUsage $usage): array
    {
        $remaining = $usage->isUnlimited() ? null : max(0, $usage->allowed - $usage->used);
        $progress = $usage->isUnlimited() ? 0 : (int) min(100, ($usage->allowed > 0 ? round(($usage->used / $usage->allowed) * 100) : 0));

        return [
            'plan' => $usage->plan,
            'plan_label' => $this->subscriptionService->getPlanLabel($usage->plan),
            'used' => $usage->used,
            'limit' => $usage->allowed,
            'limit_display' => $usage->isUnlimited() ? 'Unlimited' : $usage->allowed,
            'remaining' => $remaining,
            'progress' => $progress,
            'last_reset_at' => $usage->last_reset_at,
            'next_reset_at' => $usage->next_reset_at,
            'reset_in_human' => $usage->next_reset_at ? $usage->next_reset_at->diffForHumans(null, true) : 'No reset required',
        ];
    }

    protected function now(): Carbon
    {
        return Carbon::now();
    }
}
