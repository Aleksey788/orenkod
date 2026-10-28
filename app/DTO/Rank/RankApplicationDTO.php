<?php

namespace App\DTO\Rank;

readonly class RankApplicationDTO
{
    public function __construct(
        public int $athleteId,
        public int $sportRankId,
        public int $disciplineId,
        public int $competitionResultId,
        public string $requestType = 'assignment',
    ) {}
}
