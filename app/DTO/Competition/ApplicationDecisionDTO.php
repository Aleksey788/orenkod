<?php

namespace App\DTO\Competition;

readonly class ApplicationDecisionDTO
{
    public function __construct(
        public string $status,
        public ?string $rejectionReason = null,
    ) {}

    public static function approve(): self
    {
        return new self(
            status: 'approved'
        );
    }

    public static function reject(string $reason): self
    {
        return new self(
            status: 'rejected',
            rejectionReason: $reason
        );
    }
}