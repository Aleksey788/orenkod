<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatingRule extends Model
{
    protected $table = 'rating_rules';

    protected $fillable = [
        'rating_season_id',
        'category',
        'place',
        'points',
    ];

    protected function casts(): array
    {
        return [
            'place' => 'integer',
            'points' => 'integer',
        ];
    }

    public function ratingSeason(): BelongsTo
    {
        return $this->belongsTo(RatingSeason::class);
    }
}