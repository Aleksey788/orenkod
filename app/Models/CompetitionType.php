<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionType extends Model
{
    protected $table = 'competition_types';

    protected $fillable = [
        'name',
        'description',
    ];

    public function competitions(): HasMany
    {
        return $this->hasMany(Competition::class);
    }

    public function judgeAssignments(): HasMany
    {
        return $this->hasMany(JudgeAssignment::class);
    }
}