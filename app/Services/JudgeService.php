<?php

namespace App\Services;

use App\DTO\Judge\JudgeAssignmentDTO;
use App\Models\AuditLog;
use App\Models\Competition;
use App\Models\Judge;
use App\Models\JudgeAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JudgeService
{
    public const RELATIONS = ['judge.user:id,first_name,last_name,middle_name'];

    public function assign(JudgeAssignmentDTO $dto, User $actor): JudgeAssignment
    {
        return DB::transaction(function () use ($dto, $actor): JudgeAssignment {
            $competition = Competition::query()->lockForUpdate()->findOrFail($dto->competitionId);
            $this->ensureEditable($competition);
            $judge = Judge::query()->with('user')->findOrFail($dto->judgeId);
            if (! $judge->user->is_active) {
                throw ValidationException::withMessages(['judge_id' => 'Учётная запись судьи неактивна.']);
            }
            if ($competition->judgeAssignments()->where('judge_id', $judge->id)->exists()) {
                throw ValidationException::withMessages(['judge_id' => 'Этот судья уже назначен. Используйте редактирование должности.']);
            }
            $assignment = $competition->judgeAssignments()->create([
                'judge_id' => $judge->id, 'position' => $dto->position, 'assigned_by_user_id' => $actor->id,
            ]);
            AuditLog::recordAction($assignment, $actor->id, 'judge.assigned', null,
                $assignment->only(['competition_id', 'judge_id', 'position', 'assigned_by_user_id']));

            return $assignment->load(self::RELATIONS);
        });
    }

    public function update(JudgeAssignment $assignment, string $position, User $actor): JudgeAssignment
    {
        return DB::transaction(function () use ($assignment, $position, $actor): JudgeAssignment {
            $competition = Competition::query()->lockForUpdate()->findOrFail($assignment->competition_id);
            $this->ensureEditable($competition);
            $assignment = JudgeAssignment::query()->lockForUpdate()->findOrFail($assignment->id);
            $old = ['position' => $assignment->position];
            $assignment->position = $position;
            $assignment->save();
            AuditLog::recordAction($assignment, $actor->id, 'judge.assignment_updated', $old, ['position' => $position]);

            return $assignment->load(self::RELATIONS);
        });
    }

    private function ensureEditable(Competition $competition): void
    {
        if ($competition->results_published_at !== null || ! in_array($competition->status, ['draft', 'published'], true)) {
            throw ValidationException::withMessages(['competition' => 'Нельзя менять судейскую коллегию после публикации официального протокола или в этом состоянии соревнования.']);
        }
    }
}
