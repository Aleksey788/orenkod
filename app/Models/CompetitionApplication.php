<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitionApplication extends Model
{
    protected $table = 'competition_applications';

    protected $fillable = [
        'competition_id',
        'athlete_id',
        'submitted_by_user_id',
        'status',
        'reviewed_by_user_id',
        'reviewed_at',
        'rejection_reason',
    ];

    public function competition() { return $this->belongsTo(Competition::class); }
    public function athlete() { return $this->belongsTo(Athlete::class); }
    public function submitter() { return $this->belongsTo(User::class, 'submitted_by_user_id'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by_user_id'); }
    public function result() { return $this->hasOne(CompetitionResult::class); }
}