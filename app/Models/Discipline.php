<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Discipline extends Model
{
    protected $table = 'disciplines';

    protected $fillable = [
        'name',
    ];

    public function athletes(): BelongsToMany
    {
        return $this->belongsToMany(
            Athlete::class,
            'athlete_discipline'
        );
    }
}