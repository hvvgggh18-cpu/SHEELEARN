<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\ClearsDashboardCache;

class StudySession extends Model
{
    use HasFactory;
    use ClearsDashboardCache;

    protected $fillable = [
        'user_id',
        'session_date',
        'hours',
        'subject',
    ];

    protected $casts = [
        'session_date' => 'date',
        'hours' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
