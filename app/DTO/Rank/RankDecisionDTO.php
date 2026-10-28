<?php

namespace App\DTO\Rank;

use Illuminate\Support\Arr;

readonly class RankDecisionDTO
{
    /** @param array<string, mixed> $award */
    public function __construct(public string $status, public ?string $rejectionReason, public array $award) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self($data['status'], $data['rejection_reason'] ?? null,
            Arr::only($data, ['awarded_at', 'expires_at', 'order_number', 'order_date']));
    }
}
