<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Competition extends Model
{
    protected $table = 'competitions';

    protected $fillable = [
        'title',
        'discipline_id',
        'competition_type_id',
        'age_group_id',
        'level',
        'format',
        'location',
        'starts_at',
        'ends_at',
        'registration_starts_at',
        'registration_ends_at',
        'status',
        'calendar_plan',
        'calendar_plan_number',
        'regulation_path',
        'protocol_path',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'registration_starts_at' => 'datetime',
            'registration_ends_at' => 'datetime',
            'results_published_at' => 'datetime',
        ];
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function competitionType(): BelongsTo
    {
        return $this->belongsTo(CompetitionType::class);
    }

    public function ageGroup(): BelongsTo
    {
        return $this->belongsTo(AgeGroup::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by_user_id'
        );
    }

    public function applications(): HasMany
    {
        return $this->hasMany(
            CompetitionApplication::class
        );
    }

    public function results(): HasManyThrough
    {
        return $this->hasManyThrough(
            CompetitionResult::class,
            CompetitionApplication::class,
            'competition_id',
            'competition_application_id',
            'id',
            'id'
        );
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function resultsPublisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'results_published_by_user_id');
    }

    public function judgeAssignments(): HasMany
    {
        return $this->hasMany(JudgeAssignment::class);
    }
}
