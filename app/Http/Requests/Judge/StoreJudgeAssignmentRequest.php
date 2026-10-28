<?php

namespace App\Http\Requests\Judge;

use Illuminate\Foundation\Http\FormRequest;

class StoreJudgeAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'competition_id' => ['required', 'integer', 'exists:competitions,id'],
            'judge_id' => ['required', 'integer', 'exists:judges,id'],
            'position' => ['required', 'string', 'max:100'],
            'assigned_by_user_id' => ['missing'],
        ];
    }
}
