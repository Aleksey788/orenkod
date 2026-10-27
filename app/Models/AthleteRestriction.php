<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AthleteRestriction extends Model
{
    protected $table = 'athlete_restrictions';

    protected $fillable = [
        'athlete_id',
        'type',
        'reason',
        'starts_on',
        'ends_on',
        'revoked_at',
        'decision_number',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'revoked_at' => 'datetime',
        ];
    }

    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by_user_id'
        );
    }
}