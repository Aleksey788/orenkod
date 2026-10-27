<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JudgeCategory extends Model
{
    protected $table = 'judge_categories';

    protected $fillable = [
        'name',
        'code',
        'sort_order',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(JudgeCategoryApplication::class);
    }

    public function awards(): HasMany
    {
        return $this->hasMany(JudgeCategoryAward::class);
    }
}