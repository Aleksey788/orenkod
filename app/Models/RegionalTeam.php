<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegionalTeam extends Model
{
    protected $table = 'regional_teams';

    protected $fillable = [
        'rating_season_id',
        'discipline_id',
        'age_group_id',
        'status',
        'order_number',
        'order_date',
        'order_path',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
        ];
    }

    public function ratingSeason(): BelongsTo
    {
        return $this->belongsTo(RatingSeason::class);
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function ageGroup(): BelongsTo
    {
        return $this->belongsTo(AgeGroup::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by_user_id'
        );
    }

    public function members(): HasMany
    {
        return $this->hasMany(RegionalTeamMember::class);
    }
}