<?php

namespace App\DTO\Competition;

readonly class SaveCompetitionResultsDTO
{
    /** @param list<CompetitionResultDTO> $results */
    public function __construct(public array $results) {}

    /** @param array{results: list<array{athlete_id: int, score?: numeric|null, place?: int|null, result_status: string}>} $data */
    public static function fromArray(array $data): self
    {
        return new self(array_map(CompetitionResultDTO::fromArray(...), $data['results']));
    }
}
