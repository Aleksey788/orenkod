<?php

namespace App\DTO\Athlete;

readonly class AthleteFilterDTO
{
    /** @param array<string, mixed> $filters */
    public function __construct(public array $filters) {}
}
