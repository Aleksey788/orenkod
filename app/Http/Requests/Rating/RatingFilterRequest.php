<?php

namespace App\Http\Requests\Rating;

use Illuminate\Foundation\Http\FormRequest;

class RatingFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'season_id' => [$this->route('season') ? 'sometimes' : 'required', 'integer', 'exists:rating_seasons,id'],
            'discipline_id' => [$this->route('discipline') ? 'sometimes' : 'required', 'integer', 'exists:disciplines,id'],
            'age_group_id' => ['sometimes', 'nullable', 'integer', 'exists:age_groups,id'],
            'min_sport_rank_id' => ['sometimes', 'nullable', 'integer', 'exists:sport_ranks,id'],
            'as_of' => ['sometimes', 'date_format:Y-m-d', 'before_or_equal:today'],
        ];
    }
}
