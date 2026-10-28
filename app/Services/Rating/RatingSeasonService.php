<?php

namespace App\Services\Rating;

use App\DTO\Rating\RatingFilterDTO;
use App\DTO\Rating\RatingSeasonDTO;
use App\Models\AgeGroup;
use App\Models\AuditLog;
use App\Models\Discipline;
use App\Models\RatingSeason;
use App\Models\SportRank;
use Database\Seeders\RatingRuleSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RatingSeasonService
{
    public function __construct(private RatingCalculationService $calculation) {}

    public static function lockCalendar(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('LOCK TABLE rating_seasons IN SHARE ROW EXCLUSIVE MODE');
        }
    }

    public function save(?RatingSeason $season, RatingSeasonDTO $dto, int $userId): RatingSeason
    {
        return DB::transaction(function () use ($season, $dto, $userId): RatingSeason {
            self::lockCalendar();
            $season = $season ? RatingSeason::query()->lockForUpdate()->findOrFail($season->id) : new RatingSeason;
            if ($season->status === 'closed') {
                throw ValidationException::withMessages(['season' => 'Закрытый сезон нельзя редактировать.']);
            }
            $data = $dto->attributes;
            if ($season->status === 'active' && $data['status'] === 'draft') {
                throw ValidationException::withMessages(['status' => 'Активный сезон нельзя вернуть в черновик.']);
            }
            if (RatingSeason::query()->whereKeyNot($season->id ?? 0)->where(function ($query) use ($data): void {
                $query->where('year', $data['year'])->orWhere(function ($query) use ($data): void {
                    $query->where('starts_on', '<=', $data['ends_on'])->where('ends_on', '>=', $data['starts_on']);
                });
            })->exists()) {
                throw ValidationException::withMessages(['starts_on' => 'Год уже занят или интервалы сезонов пересекаются.']);
            }
            $old = $season->exists ? $season->only(array_keys($data)) : null;
            if ($season->exists && $season->regionalTeams()->exists()
                && ($season->year !== (int) $data['year'] || $season->starts_on->toDateString() !== $data['starts_on']
                    || $season->ends_on->toDateString() !== $data['ends_on'])) {
                throw ValidationException::withMessages(['season' => 'Нельзя менять календарные границы сезона с сохранёнными составами.']);
            }
            if ($data['status'] === 'closed' && $data['ends_on'] > now()->toDateString()) {
                throw ValidationException::withMessages(['status' => 'Нельзя закрыть сезон до даты окончания.']);
            }
            $created = ! $season->exists;
            $season->fill([...$data, 'is_active' => $data['status'] === 'active']);
            $season->save();
            if ($created) {
                RatingRuleSeeder::seedSeason($season);
            }
            if ($season->status === 'closed') {
                $this->snapshot($season);
            }
            AuditLog::recordAction($season, $userId, $created ? 'season.created' : 'season.updated', $old,
                [...$season->only(array_keys($data)), 'rating_version' => $season->rating_version]);

            return $season;
        });
    }

    public function recalculate(RatingSeason $season, int $userId, string $reason): RatingSeason
    {
        return DB::transaction(function () use ($season, $userId, $reason): RatingSeason {
            self::lockCalendar();
            $season = RatingSeason::query()->lockForUpdate()->findOrFail($season->id);
            if ($season->status !== 'closed') {
                throw ValidationException::withMessages(['season' => 'Активный рейтинг рассчитывается на лету; фиксация нужна закрытому сезону.']);
            }
            $old = ['rating_version' => $season->rating_version];
            $this->snapshot($season);
            AuditLog::recordAction($season, $userId, 'rating.recalculated', $old,
                ['rating_version' => $season->rating_version, 'reason' => $reason]);

            return $season;
        });
    }

    private function snapshot(RatingSeason $season): void
    {
        $snapshot = [];
        $snapshot['_sport_rank_orders'] = SportRank::query()->pluck('sort_order', 'id')->all();
        $snapshot['_age_groups'] = AgeGroup::query()->get()->keyBy('id')->map(fn (AgeGroup $group): array => ['min_age' => $group->min_age, 'max_age' => $group->max_age])->all();
        foreach (Discipline::query()->orderBy('id')->get() as $discipline) {
            $snapshot[(string) $discipline->id] = $this->calculation->calculate(
                $season, $discipline, new RatingFilterDTO(asOf: $season->ends_on->toDateString()), false
            )->all();
        }
        $season->forceFill([
            'rating_snapshot' => $snapshot, 'rating_version' => $season->rating_version + 1, 'ratings_calculated_at' => now(),
        ])->save();
    }
}
