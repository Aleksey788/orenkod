<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AthleteRank extends Model
{
    protected $table = 'athlete_ranks';

    protected $fillable = [
        'athlete_id',
        'sport_rank_id',
        'discipline_id',
        'rank_application_id',
        'awarded_at',
        'expires_at',
        'order_number',
        'order_date',
        'order_path',
    ];

    public function athlete() { return $this->belongsTo(Athlete::class); }
    public function sportRank() { return $this->belongsTo(SportRank::class); }
    public function discipline() { return $this->belongsTo(Discipline::class); }
    public function rankApplication() { return $this->belongsTo(RankApplication::class); }
}