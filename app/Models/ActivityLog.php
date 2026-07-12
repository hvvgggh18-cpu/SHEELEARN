<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\ClearsDashboardCache;

class ActivityLog extends Model
{
    use HasFactory;
    use ClearsDashboardCache;

    protected $fillable = [
        'user_id',
        'action',
        'subject',
        'details',
        'type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
