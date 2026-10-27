<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class CoachAthlete extends Pivot
{
    protected $table = 'coach_athlete';

    public $incrementing = true;

    protected $fillable = [
        'coach_id',
        'athlete_id',
        'discipline_id',
        'status',
        'confirmed_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(
            CoachAthleteHistory::class,
            'coach_athlete_id'
        );
    }
}