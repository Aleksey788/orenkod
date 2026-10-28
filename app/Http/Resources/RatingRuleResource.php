<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'rating_season_id' => $this->rating_season_id,
            'category' => $this->category, 'place' => $this->place, 'points' => $this->points,
            'calculation_policy' => 'live_until_season_closed'];
    }
}
