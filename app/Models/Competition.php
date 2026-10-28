<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    use HasFactory;

    protected $table = 'competitions';

    protected $fillable = [
        'title',
        'discipline_id',
        'competition_type_id',
        'age_group_id',
        'level',
        'format',
        'location',
        'starts_at',
        'ends_at',
        'registration_starts_at',
        'registration_ends_at',
        'status',
        'calendar_plan',
        'calendar_plan_number',
        'regulation_path',
        'protocol_path',
        'created_by_user_id',
    ];

    // Связи с другими таблицами
    public function discipline()
    {
        return $this->belongsTo(Discipline::class);
    }

    public function competitionType()
    {
        return $this->belongsTo(CompetitionType::class);
    }

    public function ageGroup()
    {
        return $this->belongsTo(AgeGroup::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}