<?php

namespace App\DTO\Coach;

readonly class CoachLinkDTO
{
    public function __construct(public int $athleteId, public int $disciplineId) {}
}
