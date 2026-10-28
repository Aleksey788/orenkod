<?php

namespace App\Services\Competition;

use App\DTO\Competition\ApplicationDecisionDTO;
use App\Models\Athlete;
use App\Models\Coach;
use App\Models\Competition;
use App\Models\CompetitionApplication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompetitionApplicationService
{
    public function __construct(private CompetitionResultService $results) {}

    /**
     * Подача заявки спортсменом или тренером.
     */
    public function apply(
        Competition $competition,
        Athlete $athlete,
        User $submittedBy,
        ?Coach $coach = null
    ): CompetitionApplication {

        return DB::transaction(function () use (
            $competition,
            $athlete,
            $submittedBy,
            $coach
        ) {

            // Блокируем соревнование на время проверки.
            // Это помогает предотвратить гонки при подаче заявок.
            $competition = Competition::query()
                ->whereKey($competition->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->results->ensureUnpublished($competition);

            // 1. Соревнование должно быть опубликовано
            if ($competition->status !== 'published') {
                throw ValidationException::withMessages([
                    'competition' => 'Соревнование не опубликовано',
                ]);
            }

            // 2. Проверяем период регистрации
            $now = now();

            if (
                $now->lt($competition->registration_starts_at) ||
                $now->gt($competition->registration_ends_at)
            ) {
                throw ValidationException::withMessages([
                    'registration' => 'Приём заявок на соревнование закрыт',
                ]);
            }

            // 3. Проверяем наличие дисциплины у спортсмена
            $hasDiscipline = $athlete->disciplines()
                ->where(
                    'disciplines.id',
                    $competition->discipline_id
                )
                ->exists();

            if (! $hasDiscipline) {
                throw ValidationException::withMessages([
                    'discipline' => 'Спортсмен не занимается этой дисциплиной',
                ]);
            }

            // 4. Проверяем возраст спортсмена
            $ageGroup = $competition->ageGroup;

            $age = (int) floor(
                Carbon::parse($athlete->birth_date)
                    ->diffInYears($competition->starts_at)
            );

            if (
                ($ageGroup->min_age !== null &&
                    $age < $ageGroup->min_age) ||
                ($ageGroup->max_age !== null &&
                    $age > $ageGroup->max_age)
            ) {
                throw ValidationException::withMessages([
                    'age' => 'Спортсмен не подходит по возрастной группе',
                ]);
            }

            // 5. Если заявку подаёт тренер —
            // проверяем подтверждённую активную связь
            if ($coach !== null) {

                if ($coach->user_id !== $submittedBy->id) {
                    abort(403, 'Это не профиль вашего тренера');
                }

                $hasConnection = DB::table('coach_athlete')
                    ->where('coach_id', $coach->id)
                    ->where('athlete_id', $athlete->id)
                    ->where(
                        'discipline_id',
                        $competition->discipline_id
                    )
                    ->where('status', 'active')
                    ->whereNotNull('confirmed_at')
                    ->exists();

                if (! $hasConnection) {
                    abort(
                        403,
                        'Нет подтверждённой связи со спортсменом'
                    );
                }
            } else {

                // Спортсмен может подать заявку только за себя
                if ($athlete->user_id !== $submittedBy->id) {
                    abort(403, 'Нельзя подать заявку за другого спортсмена');
                }
            }

            // 6. Проверяем повторную заявку
            $alreadyExists = $competition->applications()
                ->where('athlete_id', $athlete->id)
                ->exists();

            if ($alreadyExists) {
                throw ValidationException::withMessages([
                    'application' => 'Заявка на это соревнование уже существует',
                ]);
            }

            // 7. Создаём заявку
            return $competition->applications()->create([
                'athlete_id' => $athlete->id,
                'submitted_by_user_id' => $submittedBy->id,
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Рассмотрение заявки администратором.
     */
    public function decide(
        CompetitionApplication $application,
        User $reviewer,
        ApplicationDecisionDTO $dto
    ): CompetitionApplication {

        return DB::transaction(function () use (
            $application,
            $reviewer,
            $dto
        ) {

            $competition = Competition::query()
                ->whereKey($application->competition_id)
                ->lockForUpdate()
                ->firstOrFail();
            $this->results->ensureUnpublished($competition);

            $application = CompetitionApplication::query()
                ->whereKey($application->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Рассматриваем только новые заявки
            if ($application->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Эта заявка уже рассмотрена',
                ]);
            }

            $application->update([
                'status' => $dto->status,
                'reviewed_by_user_id' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => $dto->rejectionReason,
            ]);

            return $application->refresh();
        });
    }
}
