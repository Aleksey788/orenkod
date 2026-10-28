<?php

namespace App\Http\Requests\RegionalTeam;

use Illuminate\Foundation\Http\FormRequest;

class AddRegionalTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'athlete_id' => ['required', 'integer', 'exists:athletes,id'],
            'reason' => ['required', 'string', 'max:2000'],
            'included_by_user_id' => ['missing'], 'included_at' => ['missing'],
            'rating_snapshot' => ['missing'], 'removed_at' => ['missing'],
        ];
    }
}
