<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    // Указываем реальное имя таблицы в базе данных
    protected $table = 'competition_applications';

    protected $fillable = [
        'competition_id',
        'athlete_id',
        'submitted_by_user_id',
        'status',
        'reviewed_by_user_id',
        'reviewed_at',
    ];

    // --- Связи ---

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function athlete()
    {
        return $this->belongsTo(Athlete::class);
    }

    public function submittedByUser()
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function result()
    {
        return $this->hasOne(CompetitionResult::class, 'competition_application_id');
    }
}