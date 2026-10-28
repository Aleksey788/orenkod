<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AthleteResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $competition = $this->application->competition;

        return [
            'id' => $this->id,
            'competition' => [
                'id' => $competition->id,
                'title' => $competition->title,
                'discipline' => ['id' => $competition->discipline->id, 'name' => $competition->discipline->name],
                'starts_at' => $competition->starts_at->toIso8601String(),
                'ends_at' => $competition->ends_at->toIso8601String(),
                'competition_type' => ['id' => $competition->competitionType->id, 'name' => $competition->competitionType->name],
                'level' => $competition->level,
            ],
            'score' => $this->score,
            'place' => $this->place,
            'result_status' => $this->status,
            'results_published_at' => $competition->results_published_at->toIso8601String(),
            'protocol_url' => route('competitions.protocol', $competition),
        ];
    }
}
