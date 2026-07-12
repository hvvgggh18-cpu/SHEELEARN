<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\ClearsDashboardCache;
use App\Models\FlashcardDeck;

class Flashcard extends Model
{
    use HasFactory;
    use ClearsDashboardCache;

    protected $fillable = [
        'user_id',
        'deck_id',
        'subject',
        'question',
        'answer',
        'explanation',
        'example',
        'mnemonic',
        'tags',
        'card_type',
        'source',
        'source_reference',
        'difficulty',
        'due_at',
        'reviewed_at',
        'mastered',
        'accuracy',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'mastered' => 'boolean',
        'accuracy' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deck(): BelongsTo
    {
        return $this->belongsTo(FlashcardDeck::class, 'deck_id');
    }
}
