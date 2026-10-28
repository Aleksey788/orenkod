<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function judge() { return $this->belongsTo(Judge::class); }
    public function category() { return $this->belongsTo(JudgeCategory::class, 'judge_category_id'); }
    public function application() { return $this->belongsTo(JudgeCategoryApplication::class, 'judge_category_application_id'); }
}