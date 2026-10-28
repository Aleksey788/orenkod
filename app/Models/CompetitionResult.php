<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionResult extends Model
{
    use HasFactory;

    protected $table = 'competition_results';

    protected $fillable = [
        'competition_application_id',
        'place',
        'score',
        'status',
        'notes',
        'entered_by_user_id',
    ];

    // Связь с заявкой
    public function application() 
    { 
        return $this->belongsTo(CompetitionApplication::class, 'competition_application_id'); 
    }

    // Удобный доступ к спортсмену через заявку
    public function athlete()
    {
        return $this->hasOneThrough(
            Athlete::class,
            CompetitionApplication::class,
            'id', // PK в competition_applications
            'id', // PK в athletes
            'competition_application_id', // FK в competition_results
            'athlete_id' // FK в competition_applications
        );
    }

    // Удобный доступ к соревнованию через заявку
    public function competition()
    {
        return $this->hasOneThrough(
            Competition::class,
            CompetitionApplication::class,
            'id',
            'id',
            'competition_application_id',
            'competition_id'
        );
    }

    public function enteredBy() 
    { 
        return $this->belongsTo(User::class, 'entered_by_user_id'); 
    }
}