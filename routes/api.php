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
use App\Http\Controllers\ResultController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\AthleteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- 1. ПУБЛИЧНЫЕ МАРШРУТЫ (доступны без авторизации) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Публичный просмотр соревнований
Route::get('/competitions', [CompetitionController::class, 'index']);
Route::get('/competitions/{id}', [CompetitionController::class, 'show']);

// Публичные справочники и рейтинги
Route::get('/organizations', [OrganizationController::class, 'index']);
Route::get('/athletes', [AthleteController::class, 'index']);
Route::get('/athletes/{id}', [AthleteController::class, 'show']);
Route::get('/ratings', [RatingController::class, 'index']);
Route::get('/leaderboard', [ResultController::class, 'getLeaderboard']);
Route::get('/competitions/{competitionId}/results', [ResultController::class, 'getByCompetition']);
Route::get('/competitions/{competitionId}/judges', [JudgeController::class, 'getByCompetition']);


// --- 2. ЗАЩИЩЕННЫЕ МАРШРУТЫ (доступны авторизованным пользователям) ---
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Авторизация / Профиль
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/switch-role', [AuthController::class, 'switchRole']);

    // Управление соревнованиями (Федерация)
    Route::post('/competitions', [CompetitionController::class, 'store']);
    Route::put('/competitions/{id}', [CompetitionController::class, 'update']);
    Route::delete('/competitions/{id}', [CompetitionController::class, 'destroy']);

    // Заявки на соревнования
    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::post('/applications', [ApplicationController::class, 'store']);
    Route::put('/applications/{id}/status', [ApplicationController::class, 'updateStatus']);

    // Спортивные разряды (ЕВСК)
    Route::get('/rank-applications', [RankApplicationController::class, 'index']);
    Route::post('/rank-applications', [RankApplicationController::class, 'store']);
    Route::put('/rank-applications/{id}/status', [RankApplicationController::class, 'updateStatus']);

    // Тренеры и спортсмены
    Route::post('/coach/athletes/link', [CoachLinkController::class, 'store']);
    Route::put('/athlete/coach/confirm/{id}', [CoachLinkController::class, 'updateStatus']);

    // Судейство
    Route::get('/judges', [JudgeController::class, 'index']);
    Route::post('/judge-assignments', [JudgeController::class, 'store']);
    Route::put('/judges/{id}/application-status', [JudgeController::class, 'updateJudgeApplication']);

    // Организации и спортсмены (создание/редактирование)
    Route::post('/organizations', [OrganizationController::class, 'store']);
    Route::post('/athletes', [AthleteController::class, 'store']);
    Route::put('/athletes/{id}', [AthleteController::class, 'update']);

    // Рейтинги и сборная (управление)
    Route::post('/ratings/team-order', [RatingController::class, 'generateTeamOrder']);

    Route::post('/results', [ResultController::class, 'storeOrUpdate']);
    Route::get('/leaderboard', [ResultController::class, 'getLeaderboard']);
});