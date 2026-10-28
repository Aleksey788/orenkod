<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionalTeamMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $snapshot = $this->rating_snapshot ?? [];

        return ['id' => $this->id, 'athlete_id' => $this->athlete_id,
            'included_at' => $this->included_at?->toDateString(),
            'full_name' => $snapshot['full_name'] ?? null, 'organization' => $snapshot['organization'] ?? null,
            'rating_points_at_inclusion' => $snapshot['total_points'] ?? null,
            'rating_rank_at_inclusion' => $snapshot['rating_rank'] ?? null,
            'rating_as_of' => $snapshot['as_of'] ?? null,
            'reason' => $this->when($request->routeIs('admin.regional-teams.*'), $this->notes)];
    }
}
