<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Athlete extends Model
{
    use HasFactory;

    protected $table = 'athletes';

    protected $fillable = [
        'user_id',
        'organization_id',
        'birth_date',
        'municipality',
        'start_year',
        'training_stage',
    ];

    // --- Связи ---

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function disciplines()
    {
        return $this->belongsToMany(Discipline::class, 'athlete_discipline');
    }

    public function ranks()
    {
        return $this->hasMany(AthleteRank::class);
    }

    public function competitionApplications()
    {
        return $this->hasMany(CompetitionApplication::class);
    }
}