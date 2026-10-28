<?php

namespace App\Services;

use App\DTO\Coach\CoachLinkDTO;
use App\Models\Athlete;
use App\Models\AuditLog;
use App\Models\Coach;
use App\Models\CoachAthlete;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CoachLinkService
{
    public const RELATIONS = ['coach.user:id,first_name,last_name,middle_name', 'athlete.user:id,first_name,last_name,middle_name', 'discipline:id,name'];

    public function requireActiveLink(User $user, int $athleteId, int $disciplineId, bool $lock = false): CoachAthlete
    {
        $coach = $user->coach;
        abort_unless($coach, 403, 'Профиль тренера не найден.');
        $query = CoachAthlete::query()->where('coach_id', $coach->id)->where('athlete_id', $athleteId)
            ->where('discipline_id', $disciplineId)->where('status', 'active')->whereNotNull('confirmed_at')->whereNull('ended_at');
        if ($lock) {
            $query->lockForUpdate();
        }
        $link = $query->first();
        abort_unless($link, 403, 'Нет подтверждённой действующей связи со спортсменом по этой дисциплине.');

        return $link;
    }

    public function request(CoachLinkDTO $dto, User $actor): CoachAthlete
    {
        return DB::transaction(function () use ($dto, $actor): CoachAthlete {
            $coach = $actor->coach;
            abort_unless($coach, 403, 'Профиль тренера не найден.');
            Coach::query()->lockForUpdate()->findOrFail($coach->id);
            $athlete = Athlete::query()->lockForUpdate()->findOrFail($dto->athleteId);
            if (! $athlete->disciplines()->whereKey($dto->disciplineId)->exists()) {
                throw ValidationException::withMessages(['discipline_id' => 'Спортсмен не зарегистрирован в этой дисциплине.']);
            }
            $link = CoachAthlete::query()->firstOrNew([
                'coach_id' => $coach->id, 'athlete_id' => $athlete->id, 'discipline_id' => $dto->disciplineId,
            ]);
            if ($link->exists && in_array($link->status, ['pending', 'active'], true)) {
                throw ValidationException::withMessages(['athlete_id' => 'Запрос или действующая связь уже существуют.']);
            }
            $oldStatus = $link->exists ? $link->status : null;
            $link->fill(['status' => 'pending', 'confirmed_at' => null, 'ended_at' => null])->save();
            $this->history($link, $oldStatus, $actor, 'coach', 'Запрос тренера на подтверждение связи.');

            return $link->load(self::RELATIONS);
        });
    }

    public function decide(CoachAthlete $link, string $status, ?string $reason, User $actor): CoachAthlete
    {
        return DB::transaction(function () use ($link, $status, $reason, $actor): CoachAthlete {
            $link = $this->lock($link);
            abort_unless($link->athlete->user_id === $actor->id, 403);
            if (! in_array($status, ['active', 'rejected'], true)) {
                throw ValidationException::withMessages(['status' => 'Допустимы только подтверждение или отклонение.']);
            }
            if ($link->status === $status) {
                return $link->load(self::RELATIONS);
            }
            if ($link->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Этот запрос уже рассмотрен.']);
            }
            if ($status === 'active' && ! $link->athlete->disciplines()->whereKey($link->discipline_id)->exists()) {
                throw ValidationException::withMessages(['discipline_id' => 'Спортсмен больше не зарегистрирован в дисциплине.']);
            }
            $link->fill([
                'status' => $status, 'confirmed_at' => $status === 'active' ? now() : null,
                'ended_at' => $status === 'rejected' ? now() : null,
            ])->save();
            $this->history($link, 'pending', $actor, 'athlete', $reason);

            return $link->load(self::RELATIONS);
        });
    }

    public function end(CoachAthlete $link, string $reason, User $actor, string $activeRole): CoachAthlete
    {
        return DB::transaction(function () use ($link, $reason, $actor, $activeRole): CoachAthlete {
            $link = $this->lock($link);
            abort_unless(($activeRole === 'coach' && $link->coach->user_id === $actor->id)
                || ($activeRole === 'athlete' && $link->athlete->user_id === $actor->id), 403);
            if (! in_array($link->status, ['active', 'pending'], true)) {
                throw ValidationException::withMessages(['status' => 'Связь уже завершена или отклонена.']);
            }
            $oldStatus = $link->status;
            $link->fill(['status' => 'ended', 'ended_at' => now()])->save();
            $this->history($link, $oldStatus, $actor, $activeRole, $reason);

            return $link->load(self::RELATIONS);
        });
    }

    private function lock(CoachAthlete $link): CoachAthlete
    {
        Coach::query()->lockForUpdate()->findOrFail($link->coach_id);
        Athlete::query()->lockForUpdate()->findOrFail($link->athlete_id);

        return CoachAthlete::query()->with(['coach', 'athlete'])->lockForUpdate()->findOrFail($link->id);
    }

    private function history(CoachAthlete $link, ?string $oldStatus, User $actor, string $role, ?string $reason): void
    {
        $link->history()->create([
            'old_status' => $oldStatus, 'new_status' => $link->status, 'changed_by_user_id' => $actor->id, 'reason' => $reason,
        ]);
        AuditLog::recordAction($link, $actor->id, 'coach_link.status_changed', ['status' => $oldStatus],
            ['status' => $link->status, 'coach_id' => $link->coach_id, 'athlete_id' => $link->athlete_id,
                'discipline_id' => $link->discipline_id, 'reason' => $reason], $role);
    }
}
