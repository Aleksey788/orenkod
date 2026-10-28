<?php

namespace App\Http\Requests\Rank;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RankApplicationFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'athlete_id' => ['sometimes', 'integer', 'exists:athletes,id'],
            'discipline_id' => ['sometimes', 'integer', 'exists:disciplines,id'],
            'status' => ['sometimes', Rule::in(['pending', 'approved', 'rejected'])],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }
}
