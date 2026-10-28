<?php

namespace App\Http\Requests\Judge;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJudgeAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'position' => ['required', 'string', 'max:100'],
            'competition_id' => ['missing'], 'judge_id' => ['missing'], 'assigned_by_user_id' => ['missing'],
        ];
    }
}
