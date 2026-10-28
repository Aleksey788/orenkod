<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoachAthlete extends Model
{
    protected $table = 'coach_athlete';

    protected $fillable = [
        'coach_id',
        'athlete_id',
        'discipline_id',
        'status',
        'confirmed_at',
        'ended_at',
    ];

    public function coach() { return $this->belongsTo(Coach::class); }
    public function athlete() { return $this->belongsTo(Athlete::class); }
    public function discipline() { return $this->belongsTo(Discipline::class); }
}