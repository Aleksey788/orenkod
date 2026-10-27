<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\RankApplicationController;
use App\Http\Controllers\CoachLinkController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\JudgeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- 1. ПУБЛИЧНЫЕ МАРШРУТЫ (доступны без авторизации) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Просмотр соревнований доступен всем гостям и пользователям
Route::get('/competitions', [CompetitionController::class, 'index']);
Route::get('/competitions/{id}', [CompetitionController::class, 'show']);


// --- 2. ЗАЩИЩЕННЫЕ МАРШРУТЫ (требуют Bearer Token от Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {

    // Получить данные текущего авторизованного пользователя
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'user' => $request->user()
        ]);
    });

    // --- Управление соревнованиями (Федерация) ---
    Route::post('/competitions', [CompetitionController::class, 'store']);
    Route::put('/competitions/{id}', [CompetitionController::class, 'update']);
    Route::delete('/competitions/{id}', [CompetitionController::class, 'destroy']);

    // --- Заявки на соревнования ---
    Route::get('/applications', [ApplicationController::class, 'index']); // Поддерживает фильтры ?athlete_id=...
    Route::post('/applications', [ApplicationController::class, 'store']);
    Route::put('/applications/{id}/status', [ApplicationController::class, 'updateStatus']);

    // --- Спортивные разряды (ЕВСК) ---
    Route::get('/rank-applications', [RankApplicationController::class, 'index']);
    Route::post('/rank-applications', [RankApplicationController::class, 'store']);
    Route::put('/rank-applications/{id}/status', [RankApplicationController::class, 'updateStatus']);

    // --- Связи Тренер <-> Спортсмен ---
    Route::get('/coach-links', [CoachLinkController::class, 'index']);
    Route::post('/coach-links', [CoachLinkController::class, 'store']);
    Route::put('/coach-links/{id}/status', [CoachLinkController::class, 'updateStatus']);

    // --- Рейтинги и сборная ---
    Route::get('/ratings', [RatingController::class, 'index']);
    Route::post('/ratings/team-order', [RatingController::class, 'generateTeamOrder']);

    // --- Судейская система ---
    Route::get('/judges', [JudgeController::class, 'index']);
    Route::post('/judges/assignments', [JudgeController::class, 'storeAssignment']);
    Route::put('/judges/{id}/application-status', [JudgeController::class, 'updateJudgeApplication']);

    // Просмотр организаций и спортсменов
    Route::get('/organizations', [OrganizationController::class, 'index']);
    Route::get('/athletes', [AthleteController::class, 'index']);
    Route::get('/athletes/{id}', [AthleteController::class, 'show']);

    // Защищенные маршруты (только с токеном)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/organizations', [OrganizationController::class, 'store']);
        Route::post('/athletes', [AthleteController::class, 'store']);
        Route::put('/athletes/{id}', [AthleteController::class, 'update']);
    });
});