<?php

namespace App\Http\Requests\Rank;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRankApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'athlete_id' => $this->routeIs('coach.rank-applications.store')
                ? ['required', 'integer', 'exists:athletes,id'] : ['missing'],
            'sport_rank_id' => ['required', 'integer', 'exists:sport_ranks,id'],
            'discipline_id' => ['required', 'integer', 'exists:disciplines,id'],
            'competition_result_id' => ['required', 'integer', 'exists:competition_results,id'],
            'request_type' => ['sometimes', Rule::in(['assignment', 'confirmation'])],
            'status' => ['missing'], 'submitted_by_user_id' => ['missing'],
            'reviewed_by_user_id' => ['missing'], 'reviewed_at' => ['missing'], 'rejection_reason' => ['missing'],
        ];
    }
}
