<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JudgeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => trim(implode(' ', array_filter([$this->user->last_name, $this->user->first_name, $this->user->middle_name]))),
            'categories' => $this->whenLoaded('categoryAwards', fn () => $this->categoryAwards->map(fn ($award): array => [
                'id' => $award->judgeCategory->id, 'name' => $award->judgeCategory->name,
                'awarded_at' => $award->awarded_at->toDateString(), 'expires_at' => $award->expires_at?->toDateString(),
            ])->all()),
        ];
    }
}
