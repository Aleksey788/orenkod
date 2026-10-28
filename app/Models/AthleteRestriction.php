<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AthleteRestriction extends Model
{
    protected $table = 'athlete_restrictions';

    protected $fillable = [
        'athlete_id',
        'type',
        'reason',
        'starts_on',
        'ends_on',
        'revoked_at',
        'decision_number',
        'created_by_user_id',
    ];

    public function athlete() { return $this->belongsTo(Athlete::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by_user_id'); }
}