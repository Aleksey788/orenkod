<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrganizationService
{
    public function create(array $data, ?int $userId = null): Organization
    {
        return DB::transaction(function () use ($data, $userId): Organization {
            $organization = Organization::create($data);
            if ($userId !== null) {
                AuditLog::recordAction($organization, $userId, 'organization.created', null, $organization->only(array_keys($data)));
            }

            return $organization;
        });
    }

    public function update(
        Organization $organization,
        array $data,
        ?int $userId = null
    ): Organization {
        return DB::transaction(function () use ($organization, $data, $userId): Organization {
            $organization = Organization::query()->lockForUpdate()->findOrFail($organization->id);
            $old = $organization->only(array_keys($data));
            $organization->update($data);
            if ($userId !== null) {
                AuditLog::recordAction($organization, $userId, 'organization.updated', $old, $organization->only(array_keys($data)));
            }

            return $organization->refresh();
        });
    }

    public function delete(Organization $organization, ?int $userId = null): void
    {
        DB::transaction(function () use ($organization, $userId): void {

            $organization = Organization::query()
                ->whereKey($organization->id)
                ->lockForUpdate()
                ->firstOrFail();

            $hasAthletes = DB::table('athletes')
                ->where('organization_id', $organization->id)
                ->exists();

            $hasCoaches = DB::table('coaches')
                ->where('organization_id', $organization->id)
                ->exists();

            if ($hasAthletes || $hasCoaches) {
                throw ValidationException::withMessages([
                    'organization' => 'Нельзя удалить организацию, к которой привязаны спортсмены или тренеры.',
                ]);
            }

            if ($userId !== null) {
                AuditLog::recordAction($organization, $userId, 'organization.deleted', $organization->only(['name', 'type', 'municipality']), null);
            }
            $organization->delete();
        });
    }
}
