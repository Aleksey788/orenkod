<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideCoachLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['active', 'rejected'])],
            'reason' => ['nullable', 'string', 'max:2000'],
            'athlete_id' => ['missing'], 'coach_id' => ['missing'], 'discipline_id' => ['missing'],
            'confirmed_at' => ['missing'], 'ended_at' => ['missing'],
        ];
    }
}
