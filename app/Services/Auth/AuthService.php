<?php

namespace App\Services\Auth;

use App\DTO\Auth\RegisterDTO;
use App\DTO\Auth\LoginDTO;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(
        RegisterDTO $dto,
        ?string $ip
    ): User {
        return DB::transaction(function () use ($dto, $ip) {

            $role = Role::where('name', 'athlete')
                ->firstOrFail();

            $user = User::create([
                'first_name' => $dto->firstName,
                'last_name' => $dto->lastName,
                'middle_name' => $dto->middleName,
                'email' => $dto->email,
                'phone' => $dto->phone,
                'password' => Hash::make($dto->password),
                'is_active' => true,
            ]);

            // Назначаем роль спортсмена
            $user->roles()->attach($role->id);

            // Создаём профиль спортсмена
            $user->athlete()->create([
                'birth_date' => $dto->birthDate,
                'municipality' => $dto->municipality,
                'start_year' => $dto->startYear,
            ]);

            // Сохраняем согласие
            $user->consents()->create([
                'consent_type' => 'self',
                'consent_version' => 'v1',
                'agreed_at' => now(),
                'ip_address' => $ip,
            ]);

            return $user;
        });
    }

    public function login(LoginDTO $dto): User
    {
        $success = Auth::attempt([
            'email' => mb_strtolower(trim($dto->email)),
            'password' => $dto->password,
            'is_active' => true,
        ], $dto->remember);

        if (!$success) {
            throw ValidationException::withMessages([
                'email' => 'Неверный email или пароль',
            ]);
        }

        return Auth::user();
    }
}