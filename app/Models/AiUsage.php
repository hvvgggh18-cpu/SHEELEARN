<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsage extends Model
{
    protected $table = 'ai_usage';

    protected $fillable = [
        'user_id',
        'plan',
        'used',
        'allowed',
        'reset_interval_hours',
        'last_reset_at',
        'next_reset_at',
    ];

    protected $casts = [
        'used' => 'integer',
        'allowed' => 'integer',
        'reset_interval_hours' => 'integer',
        'last_reset_at' => 'datetime',
        'next_reset_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isUnlimited(): bool
    {
        return is_null($this->allowed);
    }
}
