<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected function casts(): array
    {
        return [
            'awarded_at' => 'date',
            'expires_at' => 'date',
            'order_date' => 'date',
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

    public function rankApplication(): BelongsTo
    {
        return $this->belongsTo(RankApplication::class);
    }
}