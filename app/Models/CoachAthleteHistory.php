<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachAthleteHistory extends Model
{
    protected $table = 'coach_athlete_history';

    public $timestamps = false;

    protected $fillable = [
        'coach_athlete_id',
        'old_status',
        'new_status',
        'changed_by_user_id',
        'reason',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function coachAthlete(): BelongsTo
    {
        return $this->belongsTo(
            CoachAthlete::class,
            'coach_athlete_id'
        );
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'changed_by_user_id'
        );
    }
}