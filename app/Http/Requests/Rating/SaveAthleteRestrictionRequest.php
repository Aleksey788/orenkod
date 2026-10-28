<?php

namespace App\Http\Requests\Rating;

use App\Services\Rating\AthleteEligibilityService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveAthleteRestrictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'athlete_id' => ['required', 'integer', 'exists:athletes,id'],
            'type' => ['required', Rule::in(AthleteEligibilityService::TYPES)],
            'reason' => ['required', 'string', 'max:5000'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
            'decision_number' => ['nullable', 'string', 'max:100'],
            'revoked_at' => ['missing'], 'created_by_user_id' => ['missing'],
        ];
    }
}
