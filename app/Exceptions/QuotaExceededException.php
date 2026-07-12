<?php

namespace App\Exceptions;

use Illuminate\Support\Carbon;
use RuntimeException;

class QuotaExceededException extends RuntimeException
{
    protected ?Carbon $resetAt;
    protected ?int $allowed;
    protected int $used;
    protected string $plan;

    public function __construct(string $message, string $plan, int $used, ?int $allowed, ?Carbon $resetAt = null)
    {
        parent::__construct($message);

        $this->plan = $plan;
        $this->used = $used;
        $this->allowed = $allowed;
        $this->resetAt = $resetAt;
    }

    public function getResetAt(): ?Carbon
    {
        return $this->resetAt;
    }

    public function getAllowed(): ?int
    {
        return $this->allowed;
    }

    public function getUsed(): int
    {
        return $this->used;
    }

    public function getPlan(): string
    {
        return $this->plan;
    }
}
