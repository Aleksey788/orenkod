<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RankApplication extends Model
{
    use HasFactory;

    protected $table = 'rank_applications';

    protected $fillable = [
        'athlete_id',
        'sport_rank_id',
        'discipline_id',
        'competition_result_id',
        'submitted_by_user_id',
        'request_type',
        'status',
        'reviewed_by_user_id',
        'reviewed_at',
        'rejection_reason',
    ];

    public function athlete()
    {
        return $this->belongsTo(Athlete::class);
    }

    public function sportRank()
    {
        return $this->belongsTo(SportRank::class);
    }

    public function discipline()
    {
        return $this->belongsTo(Discipline::class);
    }

    public function competitionResult()
    {
        return $this->belongsTo(CompetitionResult::class);
    }
}