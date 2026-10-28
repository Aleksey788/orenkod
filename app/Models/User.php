<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

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

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // --- Метод для генерации токена без установки Sanctum ---
    public function createToken(string $name)
    {
        $token = new \stdClass();
        // Вшиваем ID пользователя прямо в токен (например: orenkod_token_4_abcdef...)
        $token->plainTextToken = 'orenkod_token_' . $this->id . '_' . bin2hex(random_bytes(20));
        return $token;
    }

    // --- Связи с ролями и профилями ---

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function athlete()
    {
        return $this->hasOne(Athlete::class);
    }

    public function coach()
    {
        return $this->hasOne(Coach::class);
    }

    public function judge()
    {
        return $this->hasOne(Judge::class);
    }
}