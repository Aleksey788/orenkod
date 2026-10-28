<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RatingSeason extends Model
{
    protected $table = 'rating_seasons';

    protected $fillable = [
        'name',
        'year',
        'starts_on',
        'ends_on',
        'stability_coefficient',
        'min_podium_results',
        'is_active',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'stability_coefficient' => 'decimal:2',
            'min_podium_results' => 'integer',
            'is_active' => 'boolean',
            'rating_snapshot' => 'array',
            'ratings_calculated_at' => 'datetime',
            'rating_version' => 'integer',
        ];
    }

    public function rules(): HasMany
    {
        return $this->hasMany(RatingRule::class);
    }

    public function regionalTeams(): HasMany
    {
        return $this->hasMany(RegionalTeam::class);
    }
}
