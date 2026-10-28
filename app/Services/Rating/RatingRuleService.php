<?php

namespace App\Services\Rating;

use App\Models\AuditLog;
use App\Models\Competition;
use App\Models\RatingRule;
use App\Models\RatingSeason;
use App\Models\SportRank;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RatingRuleService
{
    public function rankOrder(SportRank $rank, int $sortOrder, int $userId): SportRank
    {
        return DB::transaction(function () use ($rank, $sortOrder, $userId): SportRank {
            $rank = SportRank::query()->lockForUpdate()->findOrFail($rank->id);
            $old = ['sort_order' => $rank->sort_order];
            $rank->sort_order = $sortOrder;
            $rank->save();
            AuditLog::recordAction($rank, $userId, 'rank.order_updated', $old, ['sort_order' => $sortOrder]);

            return $rank;
        });
    }

    /** @param array{rating_season_id: int, category: string, place: int, points: int} $data */
    public function save(?RatingRule $rule, array $data, int $userId): RatingRule
    {
        return DB::transaction(function () use ($rule, $data, $userId): RatingRule {
            RatingSeasonService::lockCalendar();
            $season = RatingSeason::query()->lockForUpdate()->findOrFail($data['rating_season_id']);
            if ($season->status === 'closed') {
                throw ValidationException::withMessages(['season' => 'Правила закрытого сезона неизменяемы.']);
            }
            $rule = $rule ? RatingRule::query()->lockForUpdate()->findOrFail($rule->id) : new RatingRule;
            if ($rule->exists && $rule->rating_season_id !== $season->id) {
                throw ValidationException::withMessages(['rating_season_id' => 'Нельзя переносить правило в другой сезон.']);
            }
            if ($season->rules()->where('category', $data['category'])->where('place', $data['place'])->whereKeyNot($rule->id ?? 0)->exists()) {
                throw ValidationException::withMessages(['category' => 'Правило этой категории и места уже существует.']);
            }
            $old = $rule->exists ? $rule->only(array_keys($data)) : null;
            $rule->fill($data)->save();
            AuditLog::recordAction($rule, $userId, 'rating_rule.saved', $old, $rule->only(array_keys($data)));

            return $rule;
        });
    }

    public function classify(Competition $competition, string $category, int $userId, string $reason): Competition
    {
        return DB::transaction(function () use ($competition, $category, $userId, $reason): Competition {
            RatingSeasonService::lockCalendar();
            $competition = Competition::query()->lockForUpdate()->findOrFail($competition->id);
            if ((RatingCategory::LEVELS[$category] ?? null) !== $competition->level) {
                throw ValidationException::withMessages(['rating_category' => 'Категория не соответствует уровню соревнования.']);
            }
            if (RatingSeason::query()->where('status', 'closed')->where('starts_on', '<=', $competition->starts_at->toDateString())
                ->where('ends_on', '>=', $competition->starts_at->toDateString())->exists()) {
                throw ValidationException::withMessages(['season' => 'Классификация соревнований закрытого сезона зафиксирована.']);
            }
            $old = ['rating_category' => $competition->rating_category];
            $competition->forceFill(['rating_category' => $category])->save();
            AuditLog::recordAction($competition, $userId, 'competition.classified', $old,
                ['rating_category' => $category, 'reason' => $reason]);

            return $competition;
        });
    }
}
