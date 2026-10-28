<?php

namespace App\DTO\Judge;

readonly class JudgeAssignmentDTO
{
    public function __construct(public int $competitionId, public int $judgeId, public string $position) {}
}
