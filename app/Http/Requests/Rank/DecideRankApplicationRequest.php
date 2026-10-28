<?php

namespace App\Http\Requests\Rank;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideRankApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'rejection_reason' => ['nullable', 'required_if:status,rejected', 'prohibited_unless:status,rejected', 'string', 'max:5000'],
            'awarded_at' => ['required_if:status,approved', 'prohibited_unless:status,approved', 'date_format:Y-m-d', 'before_or_equal:today'],
            'expires_at' => ['nullable', 'prohibited_unless:status,approved', 'date_format:Y-m-d', 'after_or_equal:awarded_at'],
            'order_number' => ['required_if:status,approved', 'prohibited_unless:status,approved', 'string', 'max:100'],
            'order_date' => ['required_if:status,approved', 'prohibited_unless:status,approved', 'date_format:Y-m-d', 'before_or_equal:today'],
            'reviewed_by_user_id' => ['missing'], 'reviewed_at' => ['missing'], 'athlete_id' => ['missing'],
            'sport_rank_id' => ['missing'], 'discipline_id' => ['missing'], 'competition_result_id' => ['missing'],
            'order_path' => ['missing'],
        ];
    }
}
