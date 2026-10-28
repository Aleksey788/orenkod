<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'documents';

    protected $fillable = [
        'title',
        'kind',
        'file_path',
        'file_name',
        'mime_type',
        'competition_id',
        'athlete_id',
        'judge_id',
        'regional_team_id',
        'created_by_user_id',
        'is_public',
        'published_at',
    ];

    public function competition() { return $this->belongsTo(Competition::class); }
    public function athlete() { return $this->belongsTo(Athlete::class); }
    public function judge() { return $this->belongsTo(Judge::class); }
    public function regionalTeam() { return $this->belongsTo(RegionalTeam::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by_user_id'); }
}