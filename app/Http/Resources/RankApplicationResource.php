<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RankApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $result = $this->competitionResult;
        $competition = $result?->application?->competition;
        $official = $competition && $competition->results_published_at !== null && $competition->status === 'published'
            && $result->application->athlete_id === $this->athlete_id && $competition->discipline_id === $this->discipline_id;

        return [
            'id' => $this->id, 'athlete_id' => $this->athlete_id,
            'athlete_name' => trim(implode(' ', array_filter([
                $this->athlete->user->last_name, $this->athlete->user->first_name, $this->athlete->user->middle_name,
            ]))),
            'sport_rank' => ['id' => $this->sportRank->id, 'name' => $this->sportRank->name, 'code' => $this->sportRank->code],
            'discipline' => ['id' => $this->discipline->id, 'name' => $this->discipline->name],
            'competition_result_id' => $this->competition_result_id,
            'competition_result' => $official ? [
                'id' => $result->id, 'competition_id' => $competition->id, 'title' => $competition->title,
                'place' => $result->place, 'score' => $result->score, 'result_status' => $result->status,
            ] : null,
            'request_type' => $this->request_type, 'status' => $this->status,
            'submitted_by_user_id' => $this->submitted_by_user_id, 'reviewed_by_user_id' => $this->reviewed_by_user_id,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(), 'rejection_reason' => $this->rejection_reason,
            'award' => $this->athleteRank ? [
                'id' => $this->athleteRank->id, 'awarded_at' => $this->athleteRank->awarded_at->toDateString(),
                'expires_at' => $this->athleteRank->expires_at?->toDateString(),
                'order_number' => $this->athleteRank->order_number, 'order_date' => $this->athleteRank->order_date->toDateString(),
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
