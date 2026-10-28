<?php

namespace App\DTO\Competition;

readonly class CompetitionResultDTO
{
    public function __construct(
        public int $athleteId,
        public ?string $score,
        public ?int $place,
        public string $status,
    ) {}

    /** @param array{athlete_id: int, score?: numeric|null, place?: int|null, result_status: string} $data */
    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['athlete_id'],
            isset($data['score']) ? (string) $data['score'] : null,
            isset($data['place']) ? (int) $data['place'] : null,
            $data['result_status'],
        );
    }

    /** @return array{score: ?string, place: ?int, status: string} */
    public function toArray(): array
    {
        return ['score' => $this->score, 'place' => $this->place, 'status' => $this->status];
    }
}
