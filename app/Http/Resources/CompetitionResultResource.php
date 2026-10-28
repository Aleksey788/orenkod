<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompetitionResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'position' => $this->resource['position'],
            'competition_id' => $this->resource['competition_id'],
            'competition_application_id' => $this->resource['competition_application_id'],
            'athlete_id' => $this->resource['athlete_id'],
            'full_name' => $this->resource['full_name'],
            'organization' => $this->resource['organization'],
            'discipline' => $this->resource['discipline'],
            'score' => $this->resource['score'],
            'place' => $this->resource['place'],
            'result_status' => $this->resource['result_status'],
            'created_at' => $this->resource['created_at'],
            'updated_at' => $this->resource['updated_at'],
        ];
    }
}
