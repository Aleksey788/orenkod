<?php

namespace App\Services;

use App\DTO\Rank\RankApplicationDTO;
use App\DTO\Rank\RankDecisionDTO;
use App\Models\Athlete;
use App\Models\AuditLog;
use App\Models\CompetitionResult;
use App\Models\RankApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RankApplicationService
{
    public const RELATIONS = ['athlete.user:id,first_name,last_name,middle_name', 'sportRank:id,name,code',
        'discipline:id,name', 'competitionResult.application.competition', 'athleteRank'];

    public function __construct(private CoachLinkService $links) {}

    /** @param array<string, mixed> $filters
     * @return Builder<RankApplication>
     */
    public function query(array $filters, User $actor, string $role): Builder
    {
        $query = RankApplication::query()->with(self::RELATIONS);
        if ($role === 'athlete') {
            $athlete = $actor->athlete;
            abort_unless($athlete, 403, 'Профиль спортсмена не найден.');
            $query->where('athlete_id', $athlete->id);
        } elseif ($role === 'coach') {
            abort_unless($actor->coach, 403, 'Профиль тренера не найден.');
            $query->where('submitted_by_user_id', $actor->id);
        } else {
            abort_unless($role === 'admin', 403);
        }
        foreach (['athlete_id', 'discipline_id', 'status'] as $field) {
            if (isset($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        return $query->orderByDesc('id');
    }

    public function submit(RankApplicationDTO $dto, User $actor, string $role): RankApplication
    {
        return DB::transaction(function () use ($dto, $actor, $role): RankApplication {
            $athlete = Athlete::query()->lockForUpdate()->findOrFail($dto->athleteId);
            if ($role === 'coach') {
                $this->links->requireActiveLink($actor, $athlete->id, $dto->disciplineId, true);
            } else {
                abort_unless($role === 'athlete' && $athlete->user_id === $actor->id, 403);
            }
            if (! in_array($dto->requestType, ['assignment', 'confirmation'], true)) {
                throw ValidationException::withMessages(['request_type' => 'Неизвестный тип заявления.']);
            }
            $this->validateResult($dto->competitionResultId, $athlete->id, $dto->disciplineId);
            $key = [
                'athlete_id' => $athlete->id, 'sport_rank_id' => $dto->sportRankId, 'discipline_id' => $dto->disciplineId,
                'competition_result_id' => $dto->competitionResultId, 'request_type' => $dto->requestType,
            ];
            if (RankApplication::query()->where($key)->whereIn('status', ['pending', 'approved'])->exists()) {
                throw ValidationException::withMessages(['application' => 'Такое заявление уже ожидает решения или одобрено.']);
            }
            $application = RankApplication::create([...$key, 'submitted_by_user_id' => $actor->id, 'status' => 'pending']);
            AuditLog::recordAction($application, $actor->id, 'rank_application.submitted', null, $key, $role);

            return $application->load(self::RELATIONS);
        });
    }

    public function decide(RankApplication $application, RankDecisionDTO $dto, User $actor): RankApplication
    {
        return DB::transaction(function () use ($application, $dto, $actor): RankApplication {
            Athlete::query()->lockForUpdate()->findOrFail($application->athlete_id);
            $application = RankApplication::query()->lockForUpdate()->findOrFail($application->id);
            if (! in_array($dto->status, ['approved', 'rejected'], true)) {
                throw ValidationException::withMessages(['status' => 'Допустимы только одобрение или отклонение.']);
            }
            if ($application->status === $dto->status && ($dto->status === 'rejected' || $application->athleteRank()->exists())) {
                return $application->load(self::RELATIONS);
            }
            if ($application->status !== 'pending' && ! ($application->status === 'approved' && $dto->status === 'approved')) {
                throw ValidationException::withMessages(['status' => 'Рассмотренное заявление нельзя менять обычным запросом.']);
            }
            $old = $application->only(['status', 'reviewed_by_user_id', 'reviewed_at', 'rejection_reason']);
            if ($dto->status === 'approved') {
                $this->validateResult($application->competition_result_id, $application->athlete_id, $application->discipline_id);
                foreach (['awarded_at', 'order_number', 'order_date'] as $required) {
                    if (empty($dto->award[$required])) {
                        throw ValidationException::withMessages([$required => 'Для присвоения нужны фактические реквизиты приказа.']);
                    }
                }
                $rank = $application->athleteRank()->create([
                    'athlete_id' => $application->athlete_id, 'sport_rank_id' => $application->sport_rank_id,
                    'discipline_id' => $application->discipline_id, ...$dto->award,
                ]);
                AuditLog::recordAction($rank, $actor->id, 'rank.awarded', null,
                    $rank->only(['athlete_id', 'sport_rank_id', 'discipline_id', 'rank_application_id', 'awarded_at', 'expires_at', 'order_number', 'order_date']));
            }
            $application->fill([
                'status' => $dto->status, 'reviewed_by_user_id' => $actor->id, 'reviewed_at' => now(),
                'rejection_reason' => $dto->status === 'rejected' ? $dto->rejectionReason : null,
            ])->save();
            AuditLog::recordAction($application, $actor->id, 'rank_application.reviewed', $old,
                $application->only(['status', 'reviewed_by_user_id', 'reviewed_at', 'rejection_reason']));

            return $application->load(self::RELATIONS);
        });
    }

    private function validateResult(int $resultId, int $athleteId, int $disciplineId): void
    {
        $result = CompetitionResult::query()->with('application.competition')->findOrFail($resultId);
        $application = $result->application;
        $competition = $application->competition;
        if ($application->athlete_id !== $athleteId || $application->status !== 'approved'
            || $competition->discipline_id !== $disciplineId || $competition->status !== 'published'
            || $competition->results_published_at === null || $result->status !== 'finished'
            || $result->place === null || $result->place < 1) {
            throw ValidationException::withMessages(['competition_result_id' => 'Нужен официальный завершённый результат этого спортсмена по выбранной дисциплине.']);
        }
    }
}
