<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AthleteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $private = $request->routeIs('admin.athletes.*', 'admin.reports.*', 'athlete.profile.*');

        return [
            'id' => $this->id,
            'user_id' => $this->when($private, $this->user_id),
            'full_name' => trim(implode(' ', array_filter([$this->user->last_name, $this->user->first_name, $this->user->middle_name]))),
            'birth_date' => $this->when($private, fn () => $this->birth_date->toDateString()),
            'organization' => $this->organization ? ['id' => $this->organization->id, 'name' => $this->organization->name] : null,
            'municipality' => $this->municipality,
            'start_year' => $this->start_year,
            'training_stage' => $this->training_stage,
            'disciplines' => $this->whenLoaded('disciplines', fn () => $this->disciplines
                ->map(fn ($discipline): array => ['id' => $discipline->id, 'name' => $discipline->name])->all()),
            'ranks' => $this->whenLoaded('athleteRanks', fn () => $this->athleteRanks->map(fn ($rank): array => [
                'id' => $rank->id, 'discipline_id' => $rank->discipline_id,
                'sport_rank' => ['id' => $rank->sportRank->id, 'name' => $rank->sportRank->name, 'code' => $rank->sportRank->code],
                'awarded_at' => $rank->awarded_at->toDateString(), 'expires_at' => $rank->expires_at?->toDateString(),
            ])->all()),
        ];
    }
}
