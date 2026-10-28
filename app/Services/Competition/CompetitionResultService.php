<?php

namespace App\Services\Competition;

use App\DTO\Competition\SaveCompetitionResultsDTO;
use App\Models\AuditLog;
use App\Models\Competition;
use App\Models\CompetitionApplication;
use App\Models\CompetitionResult;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompetitionResultService
{
    /** @return LengthAwarePaginator<int, CompetitionResult> */
    public function history(?int $athleteId): LengthAwarePaginator
    {
        return CompetitionResult::query()
            ->whereHas('application', fn (Builder $query) => $query->where('athlete_id', $athleteId)->where('status', 'approved'))
            ->whereHas('application.competition', fn (Builder $query) => $query->whereNotNull('results_published_at')->where('status', 'published'))
            ->with(['application.competition.discipline', 'application.competition.competitionType'])
            ->orderByDesc(
                Competition::query()->select('starts_at')
                    ->join('competition_applications', 'competitions.id', '=', 'competition_applications.competition_id')
                    ->whereColumn('competition_applications.id', 'competition_results.competition_application_id')
            )->orderByDesc('id')->paginate(15);
    }

    /** @return EloquentCollection<int, CompetitionApplication> */
    public function participants(Competition $competition): EloquentCollection
    {
        return $competition->applications()->where('status', 'approved')
            ->with(['athlete.user:id,first_name,last_name,middle_name', 'athlete.organization:id,name', 'result'])
            ->orderBy('id')->get();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function standings(Competition $competition): Collection
    {
        $competition->loadMissing('discipline');

        return $this->participants($competition)
            ->map(function (CompetitionApplication $application) use ($competition): array {
                $athlete = $application->athlete;
                $user = $athlete->user;
                $result = $application->result;

                return [
                    'competition_id' => $competition->id,
                    'competition_application_id' => $application->id,
                    'athlete_id' => $athlete->id,
                    'full_name' => trim(implode(' ', array_filter([$user->last_name, $user->first_name, $user->middle_name]))),
                    'organization' => $athlete->organization?->name,
                    'discipline' => ['id' => $competition->discipline->id, 'name' => $competition->discipline->name],
                    'application_status' => $application->status,
                    'id' => $result?->id,
                    'score' => $result?->score,
                    'place' => $result?->place,
                    'result_status' => $result?->status,
                    'created_at' => $result?->created_at?->toIso8601String(),
                    'updated_at' => $result?->updated_at?->toIso8601String(),
                ];
            })
            ->sortBy([
                fn (array $a, array $b): int => ($a['result_status'] === 'finished' ? 0 : 1) <=> ($b['result_status'] === 'finished' ? 0 : 1),
                fn (array $a, array $b): int => ($a['place'] ?? PHP_INT_MAX) <=> ($b['place'] ?? PHP_INT_MAX),
                fn (array $a, array $b): int => strcmp($a['full_name'], $b['full_name']),
                fn (array $a, array $b): int => $a['athlete_id'] <=> $b['athlete_id'],
            ])
            ->values()->map(fn (array $row, int $index): array => ['position' => $index + 1, ...$row]);
    }

    public function save(Competition $competition, SaveCompetitionResultsDTO $dto, int $userId): void
    {
        DB::transaction(function () use ($competition, $dto, $userId): void {
            $competition = Competition::query()->whereKey($competition->id)->lockForUpdate()->firstOrFail();
            $this->ensureCompleted($competition);
            $this->ensureUnpublished($competition);

            $applications = $competition->applications()->where('status', 'approved')
                ->lockForUpdate()->get()->keyBy('athlete_id');
            $seen = [];

            foreach ($dto->results as $index => $row) {
                if (! isset($applications[$row->athleteId]) || isset($seen[$row->athleteId])) {
                    throw ValidationException::withMessages([
                        "results.$index.athlete_id" => 'Нужна единственная одобренная заявка спортсмена на это соревнование.',
                    ]);
                }
                $this->validateResult($row->status, $row->place, $row->score, "results.$index");
                $seen[$row->athleteId] = true;
            }

            foreach ($dto->results as $row) {
                $result = CompetitionResult::query()->firstOrNew([
                    'competition_application_id' => $applications[$row->athleteId]->id,
                ]);
                $old = $result->exists ? $result->only(['score', 'place', 'status', 'entered_by_user_id']) : null;
                $result->fill($row->toArray());

                if (! $result->exists || $result->isDirty()) {
                    $event = $result->exists ? 'result.updated' : 'result.created';
                    $result->entered_by_user_id = $userId;
                    $result->save();
                    AuditLog::create([
                        'user_id' => $userId, 'active_role' => 'admin', 'event' => $event,
                        'subject_type' => $result->getMorphClass(), 'subject_id' => $result->id,
                        'old_values' => $old,
                        'new_values' => $result->only(['competition_application_id', 'score', 'place', 'status', 'entered_by_user_id']),
                    ]);
                }
            }
        });
    }

    public function ensureCompleted(Competition $competition): void
    {
        if ($competition->status !== 'published' || $competition->ends_at->isFuture()) {
            throw ValidationException::withMessages([
                'competition' => 'Результаты доступны для ввода только после завершения опубликованного соревнования.',
            ]);
        }
    }

    public function ensureUnpublished(Competition $competition): void
    {
        if ($competition->results_published_at !== null) {
            throw ValidationException::withMessages(['results' => 'Официальные результаты опубликованы и закрыты для изменения.']);
        }
    }

    public function ensureComplete(Competition $competition): void
    {
        $applications = $competition->applications()->where('status', 'approved')->with('result')->lockForUpdate()->get();
        if ($applications->isEmpty()) {
            throw ValidationException::withMessages(['results' => 'Нет одобренных участников.']);
        }

        foreach ($applications as $application) {
            $result = $application->result;
            if ($result === null) {
                throw ValidationException::withMessages(['results' => "Не заполнен результат спортсмена $application->athlete_id."]);
            }
            $this->validateResult($result->status, $result->place, $result->score, 'results');
        }

        if ($competition->results()->whereHas('application', fn (Builder $query) => $query->where('status', '!=', 'approved'))->exists()) {
            throw ValidationException::withMessages(['results' => 'Обнаружен результат участника без одобренной заявки.']);
        }
    }

    private function validateResult(string $status, ?int $place, ?string $score, string $key): void
    {
        $validScore = $score === null || (is_numeric($score) && is_finite((float) $score) && abs((float) $score) <= 9999999999.99);
        if (! in_array($status, CompetitionResult::STATUSES, true)
            || ! $validScore
            || ($status === 'finished' && ($place === null || $place < 1 || $place > 2147483647 || $score === null))
            || ($status !== 'finished' && $place !== null)) {
            throw ValidationException::withMessages([$key => 'Проверьте статус, баллы и место. Для finished нужны баллы и положительное место; для остальных статусов место отсутствует.']);
        }
    }
}
