<?php

namespace App\Services\Rating;

use App\Models\Athlete;
use App\Models\AthleteRestriction;
use Carbon\CarbonImmutable;

class AthleteEligibilityService
{
    public const TYPES = ['disqualification', 'suspension'];

    public function isRestricted(Athlete $athlete, CarbonImmutable $asOf): bool
    {
        $athlete->loadMissing('restrictions');
        $date = $asOf->toDateString();

        return $athlete->restrictions->contains(fn (AthleteRestriction $restriction): bool => in_array($restriction->type, self::TYPES, true)
            && $restriction->starts_on->toDateString() <= $date
            && ($restriction->ends_on === null || $restriction->ends_on->toDateString() >= $date)
            && ($restriction->revoked_at === null || $restriction->revoked_at->toDateString() > $date)
        );
    }
}
