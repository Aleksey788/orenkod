<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JudgeAssignment extends Model
{
    protected $table = 'judge_assignments';

    protected $fillable = [
        'judge_id',
        'competition_id',
        'position',
        'assigned_by_user_id',
    ];

    public function judge(): BelongsTo
    {
        return $this->belongsTo(Judge::class);
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_by_user_id'
        );
    }
}