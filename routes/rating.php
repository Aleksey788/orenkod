<?php

use App\Http\Controllers\AthleteRestrictionController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RatingRuleController;
use App\Http\Controllers\RatingSeasonController;
use App\Http\Controllers\RegionalTeamController;
use Illuminate\Support\Facades\Route;

Route::get('/ratings', [RatingController::class, 'index'])->name('ratings.index');
Route::get('/ratings/{season}/{discipline}', [RatingController::class, 'index'])->name('ratings.show');
Route::get('/ratings/{season}/{discipline}/athletes/{athlete}', [RatingController::class, 'detail'])->name('ratings.detail');
Route::get('/athlete/rating', [RatingController::class, 'mine'])->middleware(['auth', 'verified', 'role:athlete'])->name('athlete.rating');

Route::get('/regional-teams', [RegionalTeamController::class, 'publicIndex'])->name('regional-teams.index');
Route::get('/regional-teams/{team}', [RegionalTeamController::class, 'publicShow'])->name('regional-teams.show');
Route::get('/regional-teams/{team}/document', [RegionalTeamController::class, 'publicDocument'])->name('regional-teams.document');

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/rating-seasons', [RatingSeasonController::class, 'index'])->name('rating-seasons.index');
    Route::post('/rating-seasons', [RatingSeasonController::class, 'store'])->name('rating-seasons.store');
    Route::get('/rating-seasons/{season}', [RatingSeasonController::class, 'show'])->name('rating-seasons.show');
    Route::put('/rating-seasons/{season}', [RatingSeasonController::class, 'update'])->name('rating-seasons.update');
    Route::post('/rating-seasons/{season}/recalculate', [RatingSeasonController::class, 'recalculate'])->name('rating-seasons.recalculate');
    Route::get('/rating-rules', [RatingRuleController::class, 'index'])->name('rating-rules.index');
    Route::post('/rating-rules', [RatingRuleController::class, 'store'])->name('rating-rules.store');
    Route::put('/rating-rules/{rule}', [RatingRuleController::class, 'update'])->name('rating-rules.update');
    Route::patch('/competitions/{competition}/rating-category', [RatingRuleController::class, 'classify'])->name('rating-classification');
    Route::put('/sport-ranks/{rank}/rating-order', [RatingRuleController::class, 'rankOrder'])->name('rating-rank-order');
    Route::get('/athlete-restrictions', [AthleteRestrictionController::class, 'index'])->name('athlete-restrictions.index');
    Route::post('/athlete-restrictions', [AthleteRestrictionController::class, 'store'])->name('athlete-restrictions.store');
    Route::put('/athlete-restrictions/{restriction}', [AthleteRestrictionController::class, 'update'])->name('athlete-restrictions.update');
    Route::post('/athlete-restrictions/{restriction}/revoke', [AthleteRestrictionController::class, 'revoke'])->name('athlete-restrictions.revoke');
    Route::get('/regional-teams', [RegionalTeamController::class, 'index'])->name('regional-teams.index');
    Route::post('/regional-teams', [RegionalTeamController::class, 'store'])->name('regional-teams.store');
    Route::get('/regional-teams/{team}', [RegionalTeamController::class, 'show'])->name('regional-teams.show');
    Route::put('/regional-teams/{team}', [RegionalTeamController::class, 'update'])->name('regional-teams.update');
    Route::get('/regional-teams/{team}/candidates', [RegionalTeamController::class, 'candidates'])->name('regional-teams.candidates');
    Route::post('/regional-teams/{team}/members', [RegionalTeamController::class, 'addMember'])->name('regional-teams.members.store');
    Route::delete('/regional-teams/{team}/members/{member}', [RegionalTeamController::class, 'removeMember'])->name('regional-teams.members.destroy');
    Route::post('/regional-teams/{team}/publish', [RegionalTeamController::class, 'publish'])->name('regional-teams.publish');
    Route::post('/regional-teams/{team}/versions', [RegionalTeamController::class, 'newVersion'])->name('regional-teams.versions');
    Route::get('/regional-teams/{team}/document', [RegionalTeamController::class, 'document'])->name('regional-teams.document');
    Route::get('/regional-teams/{team}/order-draft', [RegionalTeamController::class, 'orderDraft'])->name('regional-teams.order-draft');
});
