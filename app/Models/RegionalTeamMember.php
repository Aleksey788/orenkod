<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegionalTeamMember extends Model
{
    protected $table = 'regional_team_members';

    protected $fillable = [
        'regional_team_id',
        'athlete_id',
        'included_at',
        'notes',
    ];

    public function team() { return $this->belongsTo(RegionalTeam::class, 'regional_team_id'); }
    public function athlete() { return $this->belongsTo(Athlete::class); }
}