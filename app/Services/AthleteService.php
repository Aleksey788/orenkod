<?php

namespace App\Services;

use App\DTO\Athlete\AthleteFilterDTO;
use App\DTO\Athlete\AthleteProfileDTO;
use App\Models\Athlete;
use App\Models\AuditLog;
use App\Models\Coach;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AthleteService
{
    public const RELATIONS = ['user:id,first_name,last_name,middle_name', 'organization:id,name', 'disciplines:id,name', 'athleteRanks.sportRank:id,name,code'];

    /** @return Builder<Athlete> */
    public function query(AthleteFilterDTO $dto, ?Coach $coach = null): Builder
    {
        $filters = $dto->filters;
        $query = Athlete::query();
        foreach (['municipality', 'organization_id', 'training_stage'] as $field) {
            if (isset($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }
        if (isset($filters['discipline_id'])) {
            $query->whereHas('disciplines', fn (Builder $query) => $query->where('disciplines.id', $filters['discipline_id']));
        }
        if (! empty($filters['search'])) {
            $search = '%'.trim($filters['search']).'%';
            $query->whereHas('user', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->whereRaw('LOWER(first_name) LIKE LOWER(?)', [$search])
                    ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', [$search])
                    ->orWhereRaw('LOWER(middle_name) LIKE LOWER(?)', [$search]);
            }));
        }
        if ($coach !== null) {
            $query->whereExists(function ($query) use ($coach, $filters): void {
                $query->selectRaw('1')->from('coach_athlete')->whereColumn('coach_athlete.athlete_id', 'athletes.id')
                    ->where('coach_id', $coach->id)->where('status', 'active')->whereNotNull('confirmed_at')->whereNull('ended_at');
                if (isset($filters['discipline_id'])) {
                    $query->where('discipline_id', $filters['discipline_id']);
                }
            });
        }

        return $query;
    }

    public function create(int $userId, AthleteProfileDTO $dto, User $actor): Athlete
    {
        return DB::transaction(function () use ($userId, $dto, $actor): Athlete {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            if (! $user->is_active || $user->athlete()->exists()) {
                throw ValidationException::withMessages(['user_id' => 'Пользователь неактивен или профиль спортсмена уже существует.']);
            }
            $athlete = $user->athlete()->create($dto->attributes);
            if ($dto->disciplineIds !== null) {
                $athlete->disciplines()->sync($dto->disciplineIds);
            }
            $user->roles()->syncWithoutDetaching([Role::query()->where('name', 'athlete')->firstOrFail()->id]);
            AuditLog::recordAction($athlete, $actor->id, 'athlete.created', null,
                ['user_id' => $userId, 'discipline_ids' => $dto->disciplineIds ?? []]);

            return $athlete->load(self::RELATIONS);
        });
    }

    public function update(Athlete $athlete, AthleteProfileDTO $dto, User $actor, string $activeRole): Athlete
    {
        return DB::transaction(function () use ($athlete, $dto, $actor, $activeRole): Athlete {
            $athlete = Athlete::query()->lockForUpdate()->findOrFail($athlete->id);
            abort_unless($activeRole === 'admin' || ($activeRole === 'athlete' && $athlete->user_id === $actor->id), 403);
            $changed = array_keys($dto->attributes);
            $oldDisciplines = $athlete->disciplines()->pluck('disciplines.id')->all();
            $athlete->fill($dto->attributes)->save();
            if ($dto->disciplineIds !== null) {
                $athlete->disciplines()->sync($dto->disciplineIds);
            }
            AuditLog::recordAction($athlete, $actor->id, 'athlete.updated', ['discipline_ids' => $oldDisciplines],
                ['changed_fields' => $changed, 'discipline_ids' => $dto->disciplineIds ?? $oldDisciplines], $activeRole);

            return $athlete->load(self::RELATIONS);
        });
    }
}
