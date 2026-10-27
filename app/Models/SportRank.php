<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SportRank extends Model
{
    protected $table = 'sport_ranks';

    protected $fillable = [
        'name',
        'code',
        'sort_order',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(RankApplication::class);
    }

    public function athleteRanks(): HasMany
    {
        return $this->hasMany(AthleteRank::class);
    }
}