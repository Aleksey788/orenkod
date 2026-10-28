<?php

use App\Http\Controllers\AthleteController;
use App\Http\Controllers\CoachLinkController;
use App\Http\Controllers\JudgeController;
use App\Http\Controllers\RankApplicationController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/athletes', [AthleteController::class, 'index'])->name('athletes.index');
    Route::post('/athletes', [AthleteController::class, 'store'])->name('athletes.store');
    Route::get('/athletes/{athlete}', [AthleteController::class, 'show'])->name('athletes.show');
    Route::patch('/athletes/{athlete}', [AthleteController::class, 'update'])->name('athletes.update');
    Route::get('/coach-links', [CoachLinkController::class, 'index'])->name('coach-links.index');
    Route::get('/rank-applications', [RankApplicationController::class, 'index'])->name('rank-applications.index');
    Route::put('/rank-applications/{application}/status', [RankApplicationController::class, 'updateStatus'])->name('rank-applications.review');
    Route::get('/judges', [JudgeController::class, 'index'])->name('judges.index');
    Route::post('/judge-assignments', [JudgeController::class, 'store'])->name('judges.assign');
    Route::put('/judge-assignments/{assignment}', [JudgeController::class, 'update'])->name('judges.update');
    Route::get('/competitions/{competition}/judges', [JudgeController::class, 'getByCompetition'])->name('judges.competition');
    Route::get('/reports/athletes-registry', [ReportController::class, 'athletesRegistry'])->name('reports.athletes-registry');
    Route::get('/reports/statistics', [ReportController::class, 'statistics'])->name('reports.statistics');
});

Route::middleware(['auth', 'verified', 'role:athlete'])->group(function () {
    Route::get('/athlete/profile', [AthleteController::class, 'profile'])->name('athlete.profile.show');
    Route::patch('/athlete/profile', [AthleteController::class, 'updateProfile'])->name('athlete.profile.update');
    Route::get('/athlete/coach-links', [CoachLinkController::class, 'index'])->name('athlete.coach-links.index');
    Route::put('/athlete/coach/confirm/{link}', [CoachLinkController::class, 'updateStatus'])->name('athlete.coach-links.confirm');
    Route::delete('/athlete/coach-links/{link}', [CoachLinkController::class, 'end'])->name('athlete.coach-links.end');
    Route::get('/athlete/rank-applications', [RankApplicationController::class, 'index'])->name('athlete.rank-applications.index');
    Route::post('/athlete/rank-applications', [RankApplicationController::class, 'store'])->name('athlete.rank-applications.store');
});

Route::middleware(['auth', 'verified', 'role:coach'])->group(function () {
    Route::get('/coach/athletes', [AthleteController::class, 'coachIndex'])->name('coach.athletes.index');
    Route::get('/coach/athletes/{athlete}', [AthleteController::class, 'coachShow'])->name('coach.athletes.show');
    Route::post('/coach/athletes/link', [CoachLinkController::class, 'store'])->name('coach.links.store');
    Route::get('/coach/links', [CoachLinkController::class, 'index'])->name('coach.links.index');
    Route::delete('/coach/links/{link}', [CoachLinkController::class, 'end'])->name('coach.links.end');
    Route::get('/coach/rank-applications', [RankApplicationController::class, 'index'])->name('coach.rank-applications.index');
    Route::post('/coach/rank-applications', [RankApplicationController::class, 'store'])->name('coach.rank-applications.store');
});

Route::get('/judge/assignments', [JudgeController::class, 'mine'])
    ->middleware(['auth', 'verified', 'role:judge'])->name('judge.assignments.index');
Route::get('/competitions/{competition}/judges', [JudgeController::class, 'getByCompetition'])->name('competitions.judges');
