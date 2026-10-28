<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JudgeAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'competition_id' => $this->competition_id,
            'judge' => new JudgeResource($this->judge), 'position' => $this->position,
            'assigned_by_user_id' => $this->when($request->routeIs('admin.judges.*'), $this->assigned_by_user_id)];
    }
}
