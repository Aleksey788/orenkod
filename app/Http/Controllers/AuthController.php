<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Регистрация нового пользователя
     */
    public function register(Request $request)
    {
        // Валидируем входящие данные (включая массив ID ролей)
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_ids' => 'required|array', // Пользователь может выбрать одну или несколько ролей
            'role_ids.*' => 'exists:roles,id', // Каждая роль должна существовать в таблице roles
        ]);

        // Создаем пользователя
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Привязываем выбранные роли через связующую таблицу role_user
        $user->roles()->attach($validated['role_ids']);

        // Генерируем токен доступа через Sanctum
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Регистрация прошла успешно.',
            'token'   => $token,
            'user'    => $user->load('roles') // Возвращаем пользователя вместе с его ролями
        ], 201);
    }

    /**
     * Авторизация (вход) пользователя
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный email или пароль.'
            ], 401);
        }

        // Создаем токен для доступа 
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Успешная авторизация.',
            'token'   => $token,
            'user'    => $user
        ], 200);
    }

    public function me(\Illuminate\Http\Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()
        ]);
    }
}