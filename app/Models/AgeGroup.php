<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgeGroup extends Model
{
    protected $table = 'age_groups';
    protected$fillable = ['name', 'min_age', 'max_age'];
}