<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JudgeCategoryApplication extends Model
{
    protected $table = 'judge_category_applications';

    protected $fillable = [
        'judge_id',
        'judge_category_id',
        'submitted_by_user_id',
        'request_type',
        'status',
        'evidence_path',
        'reviewed_by_user_id',
        'reviewed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function judge(): BelongsTo
    {
        return $this->belongsTo(Judge::class);
    }

    public function judgeCategory(): BelongsTo
    {
        return $this->belongsTo(JudgeCategory::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'submitted_by_user_id'
        );
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by_user_id'
        );
    }

    public function award(): HasOne
    {
        return $this->hasOne(JudgeCategoryAward::class);
    }
}