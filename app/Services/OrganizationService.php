<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrganizationService
{
    public function create(array $data): Organization
    {
        return Organization::create($data);
    }

    public function update(
        Organization $organization,
        array $data
    ): Organization {
        $organization->update($data);

        return $organization->refresh();
    }

    public function delete(Organization $organization): void
    {
        DB::transaction(function () use ($organization) {

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
                    'organization' =>
                        'Нельзя удалить организацию, к которой привязаны спортсмены или тренеры.',
                ]);
            }

            $organization->delete();
        });
    }
}