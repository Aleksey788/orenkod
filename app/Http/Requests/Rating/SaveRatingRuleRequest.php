<?php

namespace App\Http\Requests\Rating;

use App\Services\Rating\RatingCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRatingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating_season_id' => ['required', 'integer', 'exists:rating_seasons,id'],
            'category' => ['required', Rule::in(array_keys(RatingCategory::LEVELS))],
            'place' => ['required', 'integer', 'between:1,32767'],
            'points' => ['required', 'integer', 'between:0,1000000'],
            'created_by_user_id' => ['missing'],
        ];
    }
}
