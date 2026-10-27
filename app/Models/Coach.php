<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coach extends Model
{
    protected $table = 'coaches';

    protected $fillable = [
        'user_id',
        'organization_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function athletes(): BelongsToMany
    {
        return $this->belongsToMany(
            Athlete::class,
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
}