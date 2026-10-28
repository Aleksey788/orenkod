<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SportRank extends Model
{
    protected $table = 'sport_ranks';

    protected $fillable = [
        'name',
        'code',
        'sort_order',
    ];
}