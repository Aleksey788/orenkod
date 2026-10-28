<?php

namespace App\Http\Requests\Competition;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompetitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],

            'discipline_id' => [
                'required', 'integer', 'exists:disciplines,id',
            ],

            'competition_type_id' => [
                'required', 'integer', 'exists:competition_types,id',
            ],

            'age_group_id' => [
                'required', 'integer', 'exists:age_groups,id',
            ],

            'level' => [
                'required',
                Rule::in(['regional', 'interregional', 'national']),
            ],

            'format' => [
                'required',
                Rule::in(['offline', 'online', 'hybrid']),
            ],

            'location' => [
                'required_if:format,offline,hybrid',
                'nullable',
                'string',
                'max:255',
            ],

            'starts_at' => ['required', 'date'],

            'ends_at' => [
                'required', 'date', 'after_or_equal:starts_at',
            ],

            'registration_starts_at' => [
                'required',
                'date',
                'before_or_equal:registration_ends_at',
            ],

            'registration_ends_at' => [
                'required',
                'date',
                'before_or_equal:starts_at',
            ],

            'calendar_plan' => [
                'nullable',
                Rule::in(['regional', 'ekp']),
            ],

            'calendar_plan_number' => [
                'nullable',
                'required_with:calendar_plan',
                'string',
                'max:100',
            ],
        ];
    }
}