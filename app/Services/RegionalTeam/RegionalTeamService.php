<?php

namespace App\Services\RegionalTeam;

use App\DTO\Rating\RatingFilterDTO;
use App\DTO\RegionalTeam\RegionalTeamDTO;
use App\Models\Athlete;
use App\Models\AuditLog;
use App\Models\RatingSeason;
use App\Models\RegionalTeam;
use App\Models\RegionalTeamMember;
use App\Models\SportRank;
use App\Services\Rating\AthleteEligibilityService;
use App\Services\Rating\RatingCalculationService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class RegionalTeamService
{
    public function __construct(private RatingCalculationService $ratings, private AthleteEligibilityService $eligibility) {}

    public function save(?RegionalTeam $team, RegionalTeamDTO $dto, int $userId): RegionalTeam
    {
        return DB::transaction(function () use ($team, $dto, $userId): RegionalTeam {
            $data = $dto->attributes;
            $data['min_sport_rank_id'] ??= null;
            $season = RatingSeason::query()->lockForUpdate()->findOrFail($data['rating_season_id']);
            if ($season->status === 'draft') {
                throw ValidationException::withMessages(['season' => 'Сначала активируйте рейтинговый сезон.']);
            }
            if (isset($data['min_sport_rank_id']) && SportRank::findOrFail($data['min_sport_rank_id'])->sort_order < 1) {
                throw ValidationException::withMessages(['min_sport_rank_id' => 'Не настроен порядок старшинства разряда.']);
            }
            $team = $team ? RegionalTeam::query()->lockForUpdate()->findOrFail($team->id) : new RegionalTeam;
            $this->ensureDraft($team);
            $old = $team->exists ? $team->only(array_keys($data)) : null;
            if ($team->exists) {
                foreach (['rating_season_id', 'discipline_id', 'age_group_id', 'min_sport_rank_id'] as $field) {
                    if ((string) $team->$field !== (string) ($data[$field] ?? null)
                        && ($team->supersedes_team_id !== null || $team->members()->whereNull('removed_at')->exists())) {
                        throw ValidationException::withMessages([$field => 'Нельзя менять область отбора существующего состава; создайте отдельный состав.']);
                    }
                }
            }
            if (RegionalTeam::query()->where('rating_season_id', $season->id)->where('discipline_id', $data['discipline_id'])
                ->where('age_group_id', $data['age_group_id'])->where('version', $team->version ?? 1)->whereKeyNot($team->id ?? 0)->exists()) {
                throw ValidationException::withMessages(['team' => 'Состав этой области уже существует. Используйте создание новой версии.']);
            }
            $created = ! $team->exists;
            $team->fill($data);
            if ($created) {
                $team->forceFill(['created_by_user_id' => $userId, 'status' => 'draft', 'version' => 1]);
            }
            $team->save();
            AuditLog::recordAction($team, $userId, $created ? 'team.created' : 'team.updated', $old, $team->only(array_keys($data)));

            return $team;
        });
    }

    /** @return Collection<int, array<string, mixed>> */
    public function candidates(RegionalTeam $team): Collection
    {
        $team->loadMissing(['ratingSeason', 'discipline']);
        $rows = $this->ratings->calculate($team->ratingSeason, $team->discipline,
            new RatingFilterDTO($team->age_group_id, $team->min_sport_rank_id));
        $athletes = Athlete::query()->whereKey($rows->pluck('athlete_id'))->with('restrictions')->get()->keyBy('id');
        $included = $team->members()->whereNull('removed_at')->pluck('athlete_id')->all();

        return $rows->map(fn (array $row): array => [...$row,
            'eligible' => ! $this->eligibility->isRestricted($athletes[$row['athlete_id']], CarbonImmutable::today()),
            'included' => in_array($row['athlete_id'], $included, true),
        ]);
    }

    public function add(RegionalTeam $team, int $athleteId, string $reason, int $userId): RegionalTeamMember
    {
        return DB::transaction(function () use ($team, $athleteId, $reason, $userId): RegionalTeamMember {
            $team = $this->lockedTeam($team);
            $this->ensureDraft($team);
            Athlete::query()->lockForUpdate()->findOrFail($athleteId);
            $candidate = $this->candidates($team)->firstWhere('athlete_id', $athleteId);
            if ($candidate === null || ! $candidate['eligible']) {
                throw ValidationException::withMessages(['athlete_id' => 'Спортсмен не соответствует рейтингу, фильтрам или действующим ограничениям.']);
            }
            $member = $team->members()->firstOrNew(['athlete_id' => $athleteId]);
            if ($member->exists && $member->removed_at === null) {
                throw ValidationException::withMessages(['athlete_id' => 'Спортсмен уже включён в состав.']);
            }
            $snapshot = collect($candidate)->except(['_birth_date', '_rank_order', '_total_hundredths', 'included'])->all();
            $member->forceFill([
                'included_at' => now()->toDateString(), 'included_by_user_id' => $userId, 'notes' => $reason,
                'rating_snapshot' => $snapshot, 'removed_at' => null, 'removal_reason' => null,
            ])->save();
            AuditLog::recordAction($member, $userId, 'team.member_added', null,
                ['athlete_id' => $athleteId, 'team_id' => $team->id, 'reason' => $reason, 'total_points' => $snapshot['total_points']]);

            return $member;
        });
    }

    public function remove(RegionalTeam $team, RegionalTeamMember $member, string $reason, int $userId): void
    {
        DB::transaction(function () use ($team, $member, $reason, $userId): void {
            $team = $this->lockedTeam($team);
            $this->ensureDraft($team);
            $member = $team->members()->lockForUpdate()->findOrFail($member->id);
            if ($member->removed_at === null) {
                $member->forceFill(['removed_at' => now(), 'removal_reason' => $reason])->save();
                AuditLog::recordAction($member, $userId, 'team.member_removed', null,
                    ['team_id' => $team->id, 'athlete_id' => $member->athlete_id, 'reason' => $reason]);
            }
        });
    }

    public function newVersion(RegionalTeam $source, int $userId, string $reason): RegionalTeam
    {
        return DB::transaction(function () use ($source, $userId, $reason): RegionalTeam {
            $source = $this->lockedTeam($source);
            if ($source->status !== 'published') {
                throw ValidationException::withMessages(['team' => 'Новую версию можно создавать только из текущего опубликованного состава.']);
            }
            $next = RegionalTeam::query()->where('rating_season_id', $source->rating_season_id)
                ->where('discipline_id', $source->discipline_id)->where('age_group_id', $source->age_group_id)->max('version') + 1;
            $team = $source->replicate(['order_number', 'order_date', 'order_path', 'list_path', 'published_at', 'published_by_user_id']);
            $team->forceFill(['status' => 'draft', 'version' => $next, 'supersedes_team_id' => $source->id, 'created_by_user_id' => $userId])->save();
            foreach ($source->members()->whereNull('removed_at')->get() as $member) {
                $copy = $member->replicate();
                $copy->regional_team_id = $team->id;
                $copy->save();
            }
            AuditLog::recordAction($team, $userId, 'team.version_created', null,
                ['source_team_id' => $source->id, 'version' => $next, 'reason' => $reason]);

            return $team;
        });
    }

    public function publish(RegionalTeam $team, int $userId, RegionalTeamDocumentService $documents): RegionalTeam
    {
        $paths = [];
        try {
            return DB::transaction(function () use ($team, $userId, $documents, &$paths): RegionalTeam {
                $team = $this->lockedTeam($team);
                if ($team->status === 'published') {
                    return $team;
                }
                $this->ensureDraft($team);
                $members = $team->members()->whereNull('removed_at')->orderBy('athlete_id')->get();
                if ($members->isEmpty()) {
                    throw ValidationException::withMessages(['members' => 'Нельзя публиковать пустой состав.']);
                }
                Athlete::query()->whereKey($members->pluck('athlete_id'))->orderBy('id')->lockForUpdate()->get();
                $candidates = $this->candidates($team)->keyBy('athlete_id');
                foreach ($members as $member) {
                    if (! isset($candidates[$member->athlete_id]) || ! $candidates[$member->athlete_id]['eligible'] || $member->rating_snapshot === null) {
                        throw ValidationException::withMessages(['members' => 'Повторная проверка допуска не пройдена спортсменом '.$member->athlete_id]);
                    }
                }
                if ($team->supersedes_team_id !== null) {
                    $source = RegionalTeam::query()->lockForUpdate()->findOrFail($team->supersedes_team_id);
                    if ($source->status !== 'published') {
                        throw ValidationException::withMessages(['team' => 'Исходный состав уже заменён другой версией.']);
                    }
                    $source->status = 'superseded';
                    $source->save();
                    AuditLog::recordAction($source, $userId, 'team.superseded', null, ['replacement_team_id' => $team->id]);
                }
                $team->forceFill(['status' => 'published', 'published_at' => now(), 'published_by_user_id' => $userId]);
                $documents->store($team, $paths);
                $team->save();
                AuditLog::recordAction($team, $userId, 'team.published', null,
                    ['version' => $team->version, 'member_count' => $members->count()]);

                return $team;
            });
        } catch (Throwable $exception) {
            foreach ($paths as $path) {
                Storage::disk('team_documents')->delete($path);
            }
            throw $exception;
        }
    }

    private function lockedTeam(RegionalTeam $team): RegionalTeam
    {
        RatingSeason::query()->lockForUpdate()->findOrFail($team->rating_season_id);

        return RegionalTeam::query()->lockForUpdate()->findOrFail($team->id);
    }

    private function ensureDraft(RegionalTeam $team): void
    {
        if ($team->exists && $team->status !== 'draft') {
            throw ValidationException::withMessages(['team' => 'Опубликованный состав неизменяем. Создайте новую версию.']);
        }
    }
}
