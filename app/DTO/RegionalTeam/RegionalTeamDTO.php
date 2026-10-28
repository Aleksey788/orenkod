<?php

namespace App\DTO\RegionalTeam;

readonly class RegionalTeamDTO
{
    /** @param array{name: string, rating_season_id: int, discipline_id: int, age_group_id: int, min_sport_rank_id?: ?int} $attributes */
    public function __construct(public array $attributes) {}
}
