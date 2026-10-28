<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function judge() { return $this->belongsTo(Judge::class); }
    public function category() { return $this->belongsTo(JudgeCategory::class, 'judge_category_id'); }
    public function submitter() { return $this->belongsTo(User::class, 'submitted_by_user_id'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by_user_id'); }
}