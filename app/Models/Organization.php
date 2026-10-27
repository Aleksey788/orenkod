<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $table = 'organizations';

    protected $fillable = [
        'name',
        'type',
        'municipality',
        'address',
    ];

    public function athletes(): HasMany
    {
        return $this->hasMany(Athlete::class);
    }
}