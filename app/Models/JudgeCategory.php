<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JudgeCategory extends Model
{
    protected $table = 'judge_categories';

    protected $fillable = [
        'name',
        'code',
        'sort_order',
    ];
}