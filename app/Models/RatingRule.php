<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingRule extends Model
{
    protected $table = 'rating_rules';

    protected $fillable = [
        'rating_season_id',
        'category',
        'place',
        'points',
    ];

    public function season() { return $this->belongsTo(RatingSeason::class, 'rating_season_id'); }
}