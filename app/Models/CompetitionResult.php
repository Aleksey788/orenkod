<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionResult extends Model
{
    protected $table = 'competition_results';

    protected $fillable = [
        'competition_application_id',
        'place',
        'score',
        'status',
        'notes',
        'entered_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'place' => 'integer',
            'score' => 'decimal:2',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(
            CompetitionApplication::class,
            'competition_application_id'
        );
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'entered_by_user_id'
        );
    }
}