<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompetitionApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'competition_id' => $this->competition_id,

            'competition' => $this->whenLoaded(
                'competition',
                fn () => [
                    'id' => $this->competition->id,
                    'title' => $this->competition->title,
                    'starts_at' => $this->competition
                        ->starts_at?->toIso8601String(),
                ]
            ),

            'athlete_id' => $this->athlete_id,

            'athlete' => $this->whenLoaded(
                'athlete',
                fn () => [
                    'id' => $this->athlete->id,
                    'first_name' => $this->athlete->user?->first_name,
                    'last_name' => $this->athlete->user?->last_name,
                    'middle_name' => $this->athlete->user?->middle_name,
                ]
            ),

            'status' => $this->status,

            'submitted_by_user_id' => $this->submitted_by_user_id,

            'reviewed_by_user_id' => $this->reviewed_by_user_id,

            'reviewed_at' => $this->reviewed_at
                ? $this->reviewed_at->toIso8601String()
                : null,

            'rejection_reason' => $this->rejection_reason,

            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}