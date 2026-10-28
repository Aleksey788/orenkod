<?php

namespace App\Services\Rating;

use App\DTO\Rating\RatingFilterDTO;
use App\Models\AgeGroup;
use App\Models\AthleteRank;
use App\Models\CompetitionResult;
use App\Models\Discipline;
use App\Models\RatingSeason;
use App\Models\SportRank;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class RatingCalculationService
{
    public function __construct(private AthleteEligibilityService $eligibility) {}

    public static function totalPoints(int $basePoints, string $coefficient): string
    {
        [$whole, $fraction] = array_pad(explode('.', $coefficient, 2), 2, '0');
        $hundredths = ((int) $whole * 100 + (int) str_pad($fraction, 2, '0')) * $basePoints;

        return intdiv($hundredths, 100).'.'.str_pad((string) ($hundredths % 100), 2, '0', STR_PAD_LEFT);
    }

    public function asOf(RatingSeason $season, RatingFilterDTO $filter): CarbonImmutable
    {
        $date = CarbonImmutable::parse($filter->asOf ?? ($season->status === 'closed'
            ? $season->ends_on->toDateString() : now()->toDateString()))->startOfDay();
        if ($season->status === 'closed' && $date->toDateString() !== $season->ends_on->toDateString()) {
            throw ValidationException::withMessages(['as_of' => 'Закрытый сезон зафиксирован на дату его окончания.']);
        }

        return $date;
    }

    /** @return Collection<int, array<string, mixed>> */
    public function calculate(RatingSeason $season, Discipline $discipline, RatingFilterDTO $filter, bool $useSnapshot = true): Collection
    {
        $asOf = $this->asOf($season, $filter);
        if ($useSnapshot && $season->status === 'closed') {
            if ($season->rating_snapshot === null) {
                throw ValidationException::withMessages(['season' => 'Сначала явно пересчитайте закрытый сезон.']);
            }
            $rows = collect($season->rating_snapshot[(string) $discipline->id] ?? []);
        } else {
            $rows = $this->liveRows($season, $discipline, $asOf);
        }

        if ($filter->ageGroupId !== null) {
            $group = AgeGroup::findOrFail($filter->ageGroupId);
            $bounds = $useSnapshot && $season->status === 'closed'
                ? ($season->rating_snapshot['_age_groups'][(string) $filter->ageGroupId] ?? null)
                : ['min_age' => $group->min_age, 'max_age' => $group->max_age];
            if ($bounds === null) {
                throw ValidationException::withMessages(['age_group_id' => 'Возрастная группа отсутствует в снимке сезона. Нужен явный пересчёт.']);
            }
            $rows = $rows->filter(function (array $row) use ($bounds, $season): bool {
                $birthDate = CarbonImmutable::parse($row['_birth_date']);
                $cutoff = CarbonImmutable::parse($season->ends_on);
                $age = (int) floor($birthDate->diffInYears($cutoff, false));

                return $age >= $bounds['min_age'] && ($bounds['max_age'] === null || $age <= $bounds['max_age']);
            });
        }

        if ($filter->minSportRankId !== null) {
            $rank = SportRank::findOrFail($filter->minSportRankId);
            $rankOrder = $useSnapshot && $season->status === 'closed'
                ? ($season->rating_snapshot['_sport_rank_orders'][(string) $rank->id] ?? 0) : $rank->sort_order;
            if ($rankOrder < 1) {
                throw ValidationException::withMessages(['min_sport_rank_id' => 'Для разряда не настроен явный порядок старшинства.']);
            }
            $rows = $rows->filter(fn (array $row): bool => $row['_rank_order'] >= $rankOrder);
        }

        $position = 0;
        $ratingRank = 0;
        $previous = null;

        return $rows->sort(function (array $left, array $right): int {
            $comparison = $right['_total_hundredths'] <=> $left['_total_hundredths'];

            return $comparison ?: strcmp($left['full_name'], $right['full_name']) ?: $left['athlete_id'] <=> $right['athlete_id'];
        })->values()->map(function (array $row) use (&$position, &$ratingRank, &$previous): array {
            $position++;
            if ($previous !== $row['_total_hundredths']) {
                $ratingRank = $position;
            }
            $previous = $row['_total_hundredths'];

            return [...$row, 'rating_rank' => $ratingRank];
        });
    }

    /** @return Collection<int, array<string, mixed>> */
    private function liveRows(RatingSeason $season, Discipline $discipline, CarbonImmutable $asOf): Collection
    {
        $results = CompetitionResult::query()->whereHas('application', fn (Builder $query) => $query->where('status', 'approved'))
            ->whereHas('application.competition', fn (Builder $query) => $query
                ->where('discipline_id', $discipline->id)->where('status', 'published')->whereNotNull('results_published_at')
                ->where('starts_at', '>=', $season->starts_on->startOfDay())
                ->where('starts_at', '<', $season->ends_on->copy()->addDay()->startOfDay()))
            ->with(['application.competition.competitionType', 'application.athlete.user:id,first_name,last_name,middle_name',
                'application.athlete.organization:id,name', 'application.athlete.restrictions', 'application.athlete.athleteRanks.sportRank'])
            ->get();
        $rules = $season->rules()->get()->keyBy(fn ($rule): string => $rule->category.':'.$rule->place);
        $invalid = $results->filter(function (CompetitionResult $result): bool {
            $competition = $result->application->competition;

            return ! isset(RatingCategory::LEVELS[$competition->rating_category])
                || RatingCategory::LEVELS[$competition->rating_category] !== $competition->level;
        })->map(fn (CompetitionResult $result): int => $result->application->competition_id)->unique()->values();
        if ($invalid->isNotEmpty()) {
            throw ValidationException::withMessages(['rating_category' => 'Не настроена корректная рейтинговая категория соревнований: '.$invalid->implode(', ')]);
        }

        return $results->groupBy(fn (CompetitionResult $result): int => $result->application->athlete_id)
            ->map(function (Collection $athleteResults) use ($season, $discipline, $asOf, $rules): ?array {
                $athlete = $athleteResults->first()->application->athlete;
                if ($this->eligibility->isRestricted($athlete, $asOf)) {
                    return null;
                }
                $details = $athleteResults->sortBy('id')->map(function (CompetitionResult $result) use ($rules, $discipline): array {
                    $competition = $result->application->competition;
                    $rule = $rules->get($competition->rating_category.':'.$result->place);
                    if (! in_array($result->status, CompetitionResult::STATUSES, true)
                        || ($result->status === 'finished' && ($result->place === null || $result->place < 1))) {
                        throw ValidationException::withMessages(['results' => 'Некорректный официальный результат соревнования '.$competition->id]);
                    }
                    if ($result->status === 'finished' && $result->place <= 3 && $rule === null) {
                        throw ValidationException::withMessages(['rules' => 'Не настроены баллы за призовое место категории '.$competition->rating_category]);
                    }
                    $points = $result->status === 'finished' ? ($rule?->points ?? 0) : 0;

                    return [
                        'competition_id' => $competition->id, 'title' => $competition->title,
                        'starts_at' => $competition->starts_at->toIso8601String(),
                        'discipline_id' => $discipline->id, 'level' => $competition->level,
                        'competition_type' => $competition->competitionType->name,
                        'rating_category' => $competition->rating_category,
                        'place' => $result->place, 'result_status' => $result->status,
                        'performance_score' => $result->score, 'awarded_points' => $points,
                        'rule_id' => $result->status === 'finished' ? $rule?->id : null,
                    ];
                })->values();
                $podiums = $details->filter(fn (array $row): bool => $row['result_status'] === 'finished' && $row['place'] >= 1 && $row['place'] <= 3)
                    ->pluck('competition_id')->unique()->count();
                $base = $details->sum('awarded_points');
                $coefficient = $podiums >= $season->min_podium_results ? $season->stability_coefficient : '1.00';
                $total = self::totalPoints($base, $coefficient);
                $rankOrder = $athlete->athleteRanks->filter(fn (AthleteRank $rank): bool => $rank->discipline_id === $discipline->id && $rank->awarded_at->toDateString() <= $asOf->toDateString()
                    && ($rank->expires_at === null || $rank->expires_at->toDateString() >= $asOf->toDateString()))
                    ->max(fn (AthleteRank $rank): int => $rank->sportRank->sort_order) ?? 0;

                return [
                    'athlete_id' => $athlete->id, 'full_name' => trim(implode(' ', array_filter([
                        $athlete->user->last_name, $athlete->user->first_name, $athlete->user->middle_name,
                    ]))), 'organization' => $athlete->organization?->name,
                    'season_id' => $season->id, 'discipline_id' => $discipline->id,
                    'base_points' => $base, 'podium_competitions_count' => $podiums,
                    'stability_coefficient' => $coefficient, 'total_points' => $total,
                    'as_of' => $asOf->toDateString(), 'eligible' => true, 'results' => $details->all(),
                    '_birth_date' => $athlete->birth_date->toDateString(), '_rank_order' => $rankOrder,
                    '_total_hundredths' => (int) str_replace('.', '', $total),
                ];
            })->filter()->values();
    }
}
