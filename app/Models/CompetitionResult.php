<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitionResult extends Model
{
    protected $table = 'competition_results';

    protected $fillable = [
        'competition_application_id',
        'place',
        'score',
        'status',
        'notes',
        'entered_by_user_id',
    ];

    public function application() { return $this->belongsTo(CompetitionApplication::class, 'competition_application_id'); }
    public function enteredBy() { return $this->belongsTo(User::class, 'entered_by_user_id'); }
}