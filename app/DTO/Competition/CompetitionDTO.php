<?php

namespace App\DTO\Competition;

readonly class CompetitionDTO
{
    public function __construct(
        public string $title,
        public int $disciplineId,
        public int $competitionTypeId,
        public int $ageGroupId,
        public string $level,
        public string $format,
        public ?string $location,
        public string $startsAt,
        public string $endsAt,
        public string $registrationStartsAt,
        public string $registrationEndsAt,
        public ?string $calendarPlan,
        public ?string $calendarPlanNumber,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            disciplineId: $data['discipline_id'],
            competitionTypeId: $data['competition_type_id'],
            ageGroupId: $data['age_group_id'],
            level: $data['level'],
            format: $data['format'],
            location: $data['location'] ?? null,
            startsAt: $data['starts_at'],
            endsAt: $data['ends_at'],
            registrationStartsAt: $data['registration_starts_at'],
            registrationEndsAt: $data['registration_ends_at'],
            calendarPlan: $data['calendar_plan'] ?? null,
            calendarPlanNumber: $data['calendar_plan_number'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'discipline_id' => $this->disciplineId,
            'competition_type_id' => $this->competitionTypeId,
            'age_group_id' => $this->ageGroupId,
            'level' => $this->level,
            'format' => $this->format,
            'location' => $this->location,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'registration_starts_at' => $this->registrationStartsAt,
            'registration_ends_at' => $this->registrationEndsAt,
            'calendar_plan' => $this->calendarPlan,
            'calendar_plan_number' => $this->calendarPlanNumber,
        ];
    }
}