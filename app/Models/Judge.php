<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Judge extends Model
{
    protected $table = 'judges';

    protected $fillable = [
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoryApplications(): HasMany
    {
        return $this->hasMany(JudgeCategoryApplication::class);
    }

    public function categoryAwards(): HasMany
    {
        return $this->hasMany(JudgeCategoryAward::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(JudgeAssignment::class);
    }
}