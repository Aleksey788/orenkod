<?php

namespace App\Http\Requests\Athlete;

use Illuminate\Foundation\Http\FormRequest;

class AthleteFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'organization_id' => ['sometimes', 'integer', 'exists:organizations,id'],
            'discipline_id' => ['sometimes', 'integer', 'exists:disciplines,id'],
            'municipality' => ['sometimes', 'string', 'max:150'],
            'training_stage' => ['sometimes', 'string', 'max:100'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }
}
