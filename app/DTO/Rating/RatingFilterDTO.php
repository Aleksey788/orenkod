<?php

namespace App\DTO\Rating;

readonly class RatingFilterDTO
{
    public function __construct(
        public ?int $ageGroupId = null,
        public ?int $minSportRankId = null,
        public ?string $asOf = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['age_group_id']) ? (int) $data['age_group_id'] : null,
            isset($data['min_sport_rank_id']) ? (int) $data['min_sport_rank_id'] : null,
            $data['as_of'] ?? null,
        );
    }
}
