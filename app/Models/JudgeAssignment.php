<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JudgeAssignment extends Model
{
    protected $table = 'judge_assignments';

    protected $fillable = [
        'judge_id',
        'competition_id',
        'position',
        'assigned_by_user_id',
    ];

    public function judge() { return $this->belongsTo(Judge::class); }
    public function competition() { return $this->belongsTo(Competition::class); }
    public function assignedBy() { return $this->belongsTo(User::class, 'assigned_by_user_id'); }
}