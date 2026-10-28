<?php

namespace App\DTO\Rating;

readonly class RatingSeasonDTO
{
    /** @param array{name: string, year: int, starts_on: string, ends_on: string, status: string, stability_coefficient: numeric, min_podium_results: int} $attributes */
    public function __construct(public array $attributes) {}
}
