<?php

namespace App\Http\Requests\Rating;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRatingSeasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'between:1900,9999'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
            'status' => ['required', Rule::in(['draft', 'active', 'closed'])],
            'stability_coefficient' => ['required', 'numeric', 'decimal:0,2', 'between:1,9.99'],
            'min_podium_results' => ['required', 'integer', 'between:2,32767'],
            'rating_snapshot' => ['missing'], 'rating_version' => ['missing'],
            'ratings_calculated_at' => ['missing'], 'is_active' => ['missing'],
        ];
    }
}
