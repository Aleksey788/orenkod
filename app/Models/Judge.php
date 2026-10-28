<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Judge extends Model
{
    protected $table = 'judges';

    protected $fillable = [
        'user_id',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function categoryApplications() { return $this->hasMany(JudgeCategoryApplication::class); }
}