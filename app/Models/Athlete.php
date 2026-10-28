<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Athlete extends Model
{
    protected $table = 'athletes';

    protected $fillable = [
        'user_id',
        'organization_id',
        'birth_date',
        'municipality',
        'start_year',
        'training_stage',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'start_year' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(
            Discipline::class,
            'athlete_discipline'
        );
    }

    public function coaches(): BelongsToMany
    {
        return $this->belongsToMany(
            Coach::class,
            'coach_athlete'
        )
        ->using(CoachAthlete::class)
        ->withPivot([
            'id',
            'discipline_id',
            'status',
            'confirmed_at',
            'ended_at',
        ])
        ->withTimestamps();
    }

    public function competitionApplications(): HasMany
    {
        return $this->hasMany(
            CompetitionApplication::class
        );
    }  
    
    public function rankApplications(): HasMany
    {
        return $this->hasMany(RankApplication::class);
    }

    public function athleteRanks(): HasMany
    {
        return $this->hasMany(AthleteRank::class);
    }

    public function restrictions(): HasMany
    {
        return $this->hasMany(AthleteRestriction::class);
    }

    public function regionalTeamMemberships(): HasMany
    {
        return $this->hasMany(RegionalTeamMember::class);
    }

    
}