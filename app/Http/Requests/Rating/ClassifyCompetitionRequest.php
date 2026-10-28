<?php

namespace App\Http\Requests\Rating;

use App\Services\Rating\RatingCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassifyCompetitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating_category' => ['required', Rule::in(array_keys(RatingCategory::LEVELS))],
            'reason' => ['required', 'string', 'max:2000'],
        ];
    }
}
