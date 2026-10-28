<?php

namespace App\Http\Requests\RegionalTeam;

use Illuminate\Foundation\Http\FormRequest;

class SaveRegionalTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'rating_season_id' => ['required', 'integer', 'exists:rating_seasons,id'],
            'discipline_id' => ['required', 'integer', 'exists:disciplines,id'],
            'age_group_id' => ['required', 'integer', 'exists:age_groups,id'],
            'min_sport_rank_id' => ['nullable', 'integer', 'exists:sport_ranks,id'],
            'status' => ['missing'], 'created_by_user_id' => ['missing'], 'published_at' => ['missing'],
            'published_by_user_id' => ['missing'], 'version' => ['missing'], 'supersedes_team_id' => ['missing'],
            'list_path' => ['missing'], 'order_path' => ['missing'],
            'order_number' => ['missing'], 'order_date' => ['missing'],
        ];
    }
}
