<?php

namespace App\Http\Resources;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CompetitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return [
            'id' => $this->id,
            'title' => $this->title,

            'discipline' => $this->whenLoaded(
                'discipline',
                fn () => [
                    'id' => $this->discipline->id,
                    'name' => $this->discipline->name,
                ]
            ),

            'competition_type' => $this->whenLoaded(
                'competitionType',
                fn () => [
                    'id' => $this->competitionType->id,
                    'name' => $this->competitionType->name,
                ]
            ),

            'age_group' => $this->whenLoaded(
                'ageGroup',
                fn () => [
                    'id' => $this->ageGroup->id,
                    'name' => $this->ageGroup->name,
                ]
            ),

            'level' => $this->level,
            'rating_category' => $this->rating_category,
            'format' => $this->format,
            'location' => $this->location,

            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),

            'registration_starts_at' => $this->registration_starts_at?->toIso8601String(),

            'registration_ends_at' => $this->registration_ends_at?->toIso8601String(),

            'status' => $this->status,
            'calendar_plan' => $this->calendar_plan,
            'calendar_plan_number' => $this->calendar_plan_number,

            'regulation_url' => $this->regulation_path
                ? $disk->url($this->regulation_path)
                : null,
        ];
    }
}
