<?php

namespace App\DTO\Athlete;

use Illuminate\Support\Arr;

readonly class AthleteProfileDTO
{
    /** @param array<string, mixed> $attributes
     * @param  list<int>|null  $disciplineIds
     */
    public function __construct(public array $attributes, public ?array $disciplineIds) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            Arr::only($data, ['organization_id', 'birth_date', 'municipality', 'start_year', 'training_stage']),
            array_key_exists('discipline_ids', $data) ? array_map(intval(...), $data['discipline_ids']) : null,
        );
    }
}
