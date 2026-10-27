<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JudgeCategoryAward extends Model
{
    protected $table = 'judge_category_awards';

    protected $fillable = [
        'judge_id',
        'judge_category_id',
        'judge_category_application_id',
        'awarded_at',
        'expires_at',
        'order_number',
        'order_date',
        'order_path',
    ];

    protected function casts(): array
    {
        return [
            'awarded_at' => 'date',
            'expires_at' => 'date',
            'order_date' => 'date',
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

    public function categoryApplication(): BelongsTo
    {
        return $this->belongsTo(
            JudgeCategoryApplication::class,
            'judge_category_application_id'
        );
    }
}