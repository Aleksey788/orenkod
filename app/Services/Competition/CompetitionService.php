<?php

namespace App\Services\Competition;

use App\DTO\Competition\CompetitionDTO;
use App\Models\Competition;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class CompetitionService
{
    public function create(
        CompetitionDTO $dto,
        int $userId
    ): Competition {
        return Competition::create([
            ...$dto->toArray(),

            'status' => 'draft',
            'created_by_user_id' => $userId,
        ]);
    }

    public function update(
        Competition $competition,
        CompetitionDTO $dto
    ): Competition {
        if ($competition->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => 'Редактировать можно только черновик',
            ]);
        }

        $competition->update($dto->toArray());

        return $competition->refresh();
    }

    public function uploadRegulation(
        Competition $competition,
        UploadedFile $file
    ): Competition {
        if ($competition->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => 'Изменить положение можно только у черновика',
            ]);
        }

        $oldPath = $competition->regulation_path;

        $newPath = $file->store('regulations', 'public');

        try {
            $competition->update([
                'regulation_path' => $newPath,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newPath);

            throw $exception;
        }

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return $competition->refresh();
    }

    public function publish(
        Competition $competition
    ): Competition {
        if ($competition->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => 'Опубликовать можно только черновик',
            ]);
        }

        if (
            !$competition->regulation_path ||
            !Storage::disk('public')->exists($competition->regulation_path)
        ) {
            throw ValidationException::withMessages([
                'regulation_path' => 'Сначала загрузите положение о соревновании',
            ]);
        }

        if (
            !$competition->calendar_plan ||
            !$competition->calendar_plan_number
        ) {
            throw ValidationException::withMessages([
                'calendar_plan' => 'Укажите календарный план и номер',
            ]);
        }

        $expectedPlan = $competition->level === 'regional'
            ? 'regional'
            : 'ekp';

        if ($competition->calendar_plan !== $expectedPlan) {
            throw ValidationException::withMessages([
                'calendar_plan' => 'Календарный план не соответствует уровню соревнования',
            ]);
        }

        if ($competition->ends_at->isPast()) {
            throw ValidationException::withMessages([
                'ends_at' => 'Нельзя публиковать завершившееся соревнование',
            ]);
        }

        $competition->update([
            'status' => 'published',
        ]);

        return $competition->refresh();
    }
}