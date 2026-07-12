<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\ClearsDashboardCache;

class Quiz extends Model
{
    use HasFactory;
    use ClearsDashboardCache;

    protected $fillable = [
        'user_id',
        'subject',
        'scheduled_at',
        'completed_at',
        'score',
        'total',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'integer',
        'total' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
