<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coach extends Model
{
    protected $table = 'coaches';

    protected $fillable = [
        'user_id',
        'organization_id',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function organization() { return $this->belongsTo(Organization::class); }
    public function athletes() { 
        return $this->belongsToMany(Athlete::class, 'coach_athlete')
                    ->withPivot('discipline_id', 'status', 'confirmed_at', 'ended_at')
                    ->withTimestamps(); 
    }
}