<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RankApplication extends Model
{
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

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }

    public function sportRank(): BelongsTo
    {
        return $this->belongsTo(SportRank::class);
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function competitionResult(): BelongsTo
    {
        return $this->belongsTo(CompetitionResult::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'submitted_by_user_id'
        );
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by_user_id'
        );
    }

    public function athleteRank(): HasOne
    {
        return $this->hasOne(AthleteRank::class);
    }
}