<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return collect($this->resource)->only([
            'athlete_id', 'full_name', 'organization', 'season_id', 'discipline_id', 'base_points',
            'podium_competitions_count', 'stability_coefficient', 'total_points', 'rating_rank', 'as_of', 'eligible', 'included',
        ])->all();
    }
}
