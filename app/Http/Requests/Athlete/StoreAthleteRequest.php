<?php

namespace App\Http\Requests\Athlete;

class StoreAthleteRequest extends UpdateAthleteRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'user_id' => ['required', 'integer', 'exists:users,id', 'unique:athletes,user_id'],
            'birth_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'municipality' => ['required', 'string', 'max:150'],
            'start_year' => ['required', 'integer', 'between:1900,'.now()->year],
        ];
    }
}
