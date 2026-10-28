<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'phone',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->using(RoleUser::class)
            ->withPivot('id');
    }

    public function coach(): HasOne
    {
        return $this->hasOne(Coach::class);
    }

    public function judge(): HasOne
    {
        return $this->hasOne(Judge::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(PersonalDataConsent::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function createdDocuments(): HasMany
    {
        return $this->hasMany(
            Document::class,
            'created_by_user_id'
        );
    }

    public function athlete(): HasOne
    {
        return $this->hasOne(Athlete::class);
    }
}
