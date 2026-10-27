<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgeGroup extends Model
{
    protected $table = 'age_groups';

    protected $fillable = [
        'name',
        'min_age',
        'max_age',
    ];

    protected function casts(): array
    {
        return [
            'min_age' => 'integer',
            'max_age' => 'integer',
        ];
    }

    public function competitions(): HasMany
    {
        return $this->hasMany(Competition::class);
    }
}