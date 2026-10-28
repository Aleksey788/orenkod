<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegionalTeamMember extends Model
{
    protected $table = 'regional_team_members';

    protected $fillable = [
        'regional_team_id',
        'athlete_id',
        'included_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'included_at' => 'date',
            'rating_snapshot' => 'array',
            'removed_at' => 'datetime',
        ];
    }

    public function regionalTeam(): BelongsTo
    {
        return $this->belongsTo(RegionalTeam::class);
    }

    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }
}
