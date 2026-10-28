<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AthleteRestrictionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = match (true) {
            $this->revoked_at !== null => 'revoked',
            $this->starts_on->toDateString() > now()->toDateString() => 'scheduled',
            $this->ends_on !== null && $this->ends_on->toDateString() < now()->toDateString() => 'expired',
            default => 'active',
        };

        return ['id' => $this->id, 'athlete_id' => $this->athlete_id, 'type' => $this->type,
            'status' => $status,
            'reason' => $this->reason, 'starts_on' => $this->starts_on->toDateString(),
            'ends_on' => $this->ends_on?->toDateString(), 'revoked_at' => $this->revoked_at?->toIso8601String(),
            'decision_number' => $this->decision_number, 'created_by_user_id' => $this->created_by_user_id,
            'created_at' => $this->created_at?->toIso8601String(), 'updated_at' => $this->updated_at?->toIso8601String()];
    }
}
