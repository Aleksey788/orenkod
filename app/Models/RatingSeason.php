<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    public function rules() { return $this->hasMany(RatingRule::class); }
    public function regionalTeams() { return $this->hasMany(RegionalTeam::class); }
}