<?php

namespace App\Services;

use App\DTO\Athlete\AthleteFilterDTO;

class ReportService
{
    public function __construct(private AthleteService $athletes) {}

    /** @return array<string, mixed> */
    public function statistics(AthleteFilterDTO $filters): array
    {
        $query = $this->athletes->query($filters);
        $statistics = ['total_athletes' => (clone $query)->count()];
        foreach (['training_stage', 'municipality', 'organization_id'] as $field) {
            $statistics['by_'.$field] = (clone $query)->select($field)->selectRaw('COUNT(*) AS aggregate')
                ->groupBy($field)->orderBy($field)->get()->map(fn ($row): array => [
                    $field => $row->getAttribute($field), 'count' => (int) $row->getAttribute('aggregate'),
                ])->all();
        }

        return $statistics;
    }
}
