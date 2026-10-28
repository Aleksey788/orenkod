<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionalTeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'rating_season_id' => $this->rating_season_id,
            'discipline_id' => $this->discipline_id, 'age_group_id' => $this->age_group_id,
            'min_sport_rank_id' => $this->min_sport_rank_id, 'status' => $this->status,
            'version' => $this->version, 'supersedes_team_id' => $this->supersedes_team_id,
            'published_at' => $this->published_at?->toIso8601String(),
            'members' => RegionalTeamMemberResource::collection($this->whenLoaded('members')),
            'document_url' => in_array($this->status, ['published', 'superseded'], true)
                ? route('regional-teams.document', $this->resource) : null,
            'created_at' => $this->created_at?->toIso8601String(), 'updated_at' => $this->updated_at?->toIso8601String()];
    }
}
