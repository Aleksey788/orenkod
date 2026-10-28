<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoachLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'athlete_id' => ['required', 'integer', 'exists:athletes,id'],
            'discipline_id' => ['required', 'integer', 'exists:disciplines,id'],
            'coach_id' => ['missing'], 'status' => ['missing'],
            'confirmed_at' => ['missing'], 'ended_at' => ['missing'],
        ];
    }
}
