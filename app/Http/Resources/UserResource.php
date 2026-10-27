<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'email' => $this->email,
            'phone' => $this->phone,

            'roles' => $this->whenLoaded(
                'roles',
                fn () => $this->roles->pluck('name')->values()
            ),

            'active_role' => $request->session()
                ->get('active_role'),
        ];
    }
}