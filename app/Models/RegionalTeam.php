<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegionalTeam extends Model
{
    protected $table = 'regional_teams';

    protected $fillable = [
        'rating_season_id',
        'discipline_id',
        'age_group_id',
        'status',
        'order_number',
        'order_date',
        'order_path',
        'created_by_user_id',
    ];

    public function season() { return $this->belongsTo(RatingSeason::class, 'rating_season_id'); }
    public function discipline() { return $this->belongsTo(Discipline::class); }
    public function ageGroup() { return $this->belongsTo(AgeGroup::class); }
    public function members() { return $this->hasMany(RegionalTeamMember::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by_user_id'); }
}