<?php

namespace App\Services\Rating;

use App\Models\Athlete;
use App\Models\AthleteRestriction;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AthleteRestrictionService
{
    /** @param array<string, mixed> $data */
    public function save(?AthleteRestriction $restriction, array $data, int $userId): AthleteRestriction
    {
        return DB::transaction(function () use ($restriction, $data, $userId): AthleteRestriction {
            Athlete::query()->lockForUpdate()->findOrFail($data['athlete_id']);
            $restriction = $restriction ? AthleteRestriction::query()->lockForUpdate()->findOrFail($restriction->id) : new AthleteRestriction;
            if ($restriction->exists && ($restriction->athlete_id !== (int) $data['athlete_id'] || $restriction->revoked_at !== null)) {
                throw ValidationException::withMessages(['restriction' => 'Нельзя переносить или редактировать отменённое ограничение.']);
            }
            $old = $restriction->exists ? $restriction->only(array_keys($data)) : null;
            $restriction->fill($data);
            if (! $restriction->exists) {
                $restriction->created_by_user_id = $userId;
            }
            $restriction->save();
            AuditLog::recordAction($restriction, $userId, 'restriction.saved', $old, $restriction->only(array_keys($data)));

            return $restriction;
        });
    }

    public function revoke(AthleteRestriction $restriction, int $userId, string $reason): AthleteRestriction
    {
        return DB::transaction(function () use ($restriction, $userId, $reason): AthleteRestriction {
            Athlete::query()->lockForUpdate()->findOrFail($restriction->athlete_id);
            $restriction = AthleteRestriction::query()->lockForUpdate()->findOrFail($restriction->id);
            if ($restriction->revoked_at === null) {
                $restriction->revoked_at = now();
                $restriction->save();
                AuditLog::recordAction($restriction, $userId, 'restriction.revoked', null,
                    ['reason' => $reason, 'revoked_at' => $restriction->revoked_at->toIso8601String()]);
            }

            return $restriction;
        });
    }
}
