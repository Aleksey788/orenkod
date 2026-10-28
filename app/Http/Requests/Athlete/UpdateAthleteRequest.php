<?php

namespace App\Http\Requests\Athlete;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAthleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['missing'], 'role' => ['missing'], 'roles' => ['missing'], 'is_active' => ['missing'],
            'organization_id' => ['sometimes', 'nullable', 'integer', 'exists:organizations,id'],
            'birth_date' => ['sometimes', 'required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'municipality' => ['sometimes', 'required', 'string', 'max:150'],
            'start_year' => ['sometimes', 'required', 'integer', 'between:1900,'.now()->year],
            'training_stage' => ['sometimes', 'nullable', 'string', 'max:100'],
            'discipline_ids' => ['sometimes', 'array', 'max:100'],
            'discipline_ids.*' => ['required', 'integer', 'distinct', 'exists:disciplines,id'],
        ];
    }
}
