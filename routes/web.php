<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::middleware('guest')->group(function () {

    Route::post('/register', [
        AuthController::class, 'register'
    ])->middleware('throttle:5,1');

    Route::post('/login', [
        AuthController::class, 'login'
    ])->middleware('throttle:5,1');

});

Route::middleware('auth')->group(function () {

    Route::get('/me', [
        AuthController::class, 'me'
    ]);

    Route::post('/logout', [
        AuthController::class, 'logout'
    ]);

    Route::post('/switch-role', [
        AuthController::class, 'switchRole'
    ]);

    Route::get('/admin/dashboard', function () {
        return response()->json(['message' => 'Кабинет администратора']);
    })->middleware('role:admin');

    Route::get('/athlete/dashboard', function () {
        return response()->json(['message' => 'Кабинет спортсмена']);
    })->middleware('role:athlete');

    Route::get('/coach/dashboard', function () {
        return response()->json(['message' => 'Кабинет тренера']);
    })->middleware('role:coach');

    Route::get('/judge/dashboard', function () {
        return response()->json(['message' => 'Кабинет судьи']);
    })->middleware('role:judge');
});