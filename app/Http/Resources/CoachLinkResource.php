<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachLinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'coach' => ['id' => $this->coach_id, 'full_name' => trim(implode(' ', array_filter([
                $this->coach->user->last_name, $this->coach->user->first_name, $this->coach->user->middle_name,
            ])))],
            'athlete' => ['id' => $this->athlete_id, 'full_name' => trim(implode(' ', array_filter([
                $this->athlete->user->last_name, $this->athlete->user->first_name, $this->athlete->user->middle_name,
            ])))],
            'discipline' => ['id' => $this->discipline->id, 'name' => $this->discipline->name],
            'status' => $this->status,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'ended_at' => $this->ended_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
