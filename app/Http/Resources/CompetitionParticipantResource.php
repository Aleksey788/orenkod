<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompetitionParticipantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'competition_application_id' => $this->resource['competition_application_id'],
            'athlete_id' => $this->resource['athlete_id'],
            'full_name' => $this->resource['full_name'],
            'organization' => $this->resource['organization'],
            'discipline' => $this->resource['discipline'],
            'application_status' => $this->resource['application_status'],
            'result' => $this->resource['id'] !== null
                ? new CompetitionResultResource($this->resource) : null,
        ];
    }
}
