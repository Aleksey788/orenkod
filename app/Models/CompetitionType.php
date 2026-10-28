<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitionType extends Model
{
    protected $table = 'competition_types';
    protected $fillable = ['name', 'description'];
}