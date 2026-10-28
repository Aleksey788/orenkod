<?php

namespace App\Http\Requests\Competition;

use App\Models\CompetitionResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCompetitionResultsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'results' => ['required', 'array', 'list', 'min:1', 'max:1000'],
            'results.*' => ['required', 'array:athlete_id,score,place,result_status'],
            'results.*.athlete_id' => ['required', 'integer', 'min:1', 'distinct'],
            'results.*.result_status' => ['required', Rule::in(CompetitionResult::STATUSES)],
            'results.*.score' => [
                'nullable', 'required_if:results.*.result_status,finished',
                'numeric', 'decimal:0,2', 'between:-9999999999.99,9999999999.99',
            ],
            'results.*.place' => [
                'nullable', 'required_if:results.*.result_status,finished',
                'integer', 'min:1', 'max:2147483647',
                'prohibited_unless:results.*.result_status,finished',
            ],
            'entered_by_user_id' => ['prohibited'],
            'results_published_at' => ['prohibited'],
            'results_published_by_user_id' => ['prohibited'],
            'protocol_path' => ['prohibited'],
        ];
    }
}
