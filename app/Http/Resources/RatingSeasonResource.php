<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingSeasonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'name' => $this->name, 'year' => $this->year,
            'starts_on' => $this->starts_on->toDateString(), 'ends_on' => $this->ends_on->toDateString(),
            'status' => $this->status, 'stability_coefficient' => $this->stability_coefficient,
            'min_podium_results' => $this->min_podium_results, 'rating_version' => $this->rating_version,
            'ratings_calculated_at' => $this->ratings_calculated_at?->toIso8601String(),
            'rules' => RatingRuleResource::collection($this->whenLoaded('rules')),
            'created_at' => $this->created_at?->toIso8601String(), 'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
