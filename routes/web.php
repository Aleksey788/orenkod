<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CompetitionApplicationController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\CompetitionProtocolController;
use App\Http\Controllers\CompetitionResultController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Маршруты для гостей

Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Laravel API работает',
        'version' => '1.0',
    ]);
});

Route::middleware('guest')->group(function () {

    Route::get('/login', function () {
        return response()->json([
            'message' => 'Используйте POST /login для входа',
        ]);
    })->name('login');

    Route::post('/register', [
        AuthController::class, 'register',
    ])->middleware('throttle:5,1');

    Route::post('/login', [
        AuthController::class, 'login',
    ])->middleware('throttle:5,1');

    Route::post('/forgot-password', [
        PasswordResetController::class, 'sendLink',
    ])->middleware('throttle:3,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', function (string $token) {
        return response()->json([
            'message' => 'Передайте токен из ссылки в POST /reset-password',
        ]);
    })->name('password.reset');

    Route::post('/reset-password', [
        PasswordResetController::class, 'reset',
    ])->middleware('throttle:5,1')
        ->name('password.update');
});

// Маршруты авторизованных пользователей

Route::middleware('auth')->group(function () {

    Route::get('/me', [
        AuthController::class, 'me',
    ]);

    Route::post('/logout', [
        AuthController::class, 'logout',
    ]);

    Route::post('/switch-role', [
        AuthController::class, 'switchRole',
    ]);

    // Подтверждение электронной почты

    Route::get('/email/verify', function () {
        return response()->json([
            'message' => 'Подтвердите электронную почту',
        ]);
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}',
        function (EmailVerificationRequest $request) {

            $request->fulfill();

            return response()->json([
                'message' => 'Email успешно подтверждён',
            ]);

        }
    )->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification',
        function (Request $request) {

            if (! $request->user()->hasVerifiedEmail()) {
                $request->user()
                    ->sendEmailVerificationNotification();
            }

            return response()->json([
                'message' => 'Если требуется, письмо отправлено',
            ]);

        }
    )->middleware('throttle:6,1')
        ->name('verification.send');

    // Личные кабинеты

    Route::middleware('verified')->group(function () {

        Route::get('/admin/dashboard', function () {
            return response()->json([
                'message' => 'Кабинет администратора',
            ]);
        })->middleware('role:admin');

        Route::get('/athlete/dashboard', function () {
            return response()->json([
                'message' => 'Кабинет спортсмена',
            ]);
        })->middleware('role:athlete');

        Route::get('/coach/dashboard', function () {
            return response()->json([
                'message' => 'Кабинет тренера',
            ]);
        })->middleware('role:coach');

        Route::get('/judge/dashboard', function () {
            return response()->json([
                'message' => 'Кабинет судьи',
            ]);
        })->middleware('role:judge');

    });
});

if (app()->environment('local')) {
    Route::get('/_dev/csrf', function () {
        return response()->json([
            'csrf_token' => csrf_token(),
        ]);
    });
}

// Публичный календарь соревнований

Route::get('/competitions', [
    CompetitionController::class,
    'index',
]);

Route::get('/competitions/{competition}', [
    CompetitionController::class,
    'show',
]);

// Управление соревнованиями: только администратор

Route::middleware([
    'auth',
    'verified',
    'role:admin',
])->prefix('admin')->group(function () {

    Route::post('/competitions', [
        CompetitionController::class,
        'store',
    ]);

    Route::get('/competitions/{competition}', [
        CompetitionController::class,
        'adminShow',
    ]);

    Route::put('/competitions/{competition}', [
        CompetitionController::class,
        'update',
    ]);

    Route::post('/competitions/{competition}/regulation', [
        CompetitionController::class,
        'uploadRegulation',
    ]);

    Route::post('/competitions/{competition}/publish', [
        CompetitionController::class,
        'publish',
    ]);
});

// ============================================
// ЗАЯВКИ СПОРТСМЕНОВ
// ============================================

Route::middleware([
    'auth',
    'verified',
    'role:athlete',
])->group(function () {

    Route::post(
        '/competitions/{competition}/applications',
        [
            CompetitionApplicationController::class,
            'store',
        ]
    );

    Route::get(
        '/athlete/applications',
        [
            CompetitionApplicationController::class,
            'athleteIndex',
        ]
    );
});

// ============================================
// ЗАЯВКИ ТРЕНЕРОВ
// ============================================

Route::middleware([
    'auth',
    'verified',
    'role:coach',
])->group(function () {

    Route::post(
        '/coach/athletes/{athlete}/competitions/{competition}/applications',
        [
            CompetitionApplicationController::class,
            'coachStore',
        ]
    );

    Route::get(
        '/coach/applications',
        [
            CompetitionApplicationController::class,
            'coachIndex',
        ]
    );
});

// ============================================
// МОДЕРАЦИЯ ЗАЯВОК АДМИНИСТРАТОРОМ
// ============================================

Route::middleware([
    'auth',
    'verified',
    'role:admin',
])->group(function () {

    Route::get(
        '/admin/competitions/{competition}/applications',
        [
            CompetitionApplicationController::class,
            'adminIndex',
        ]
    );

    Route::patch(
        '/admin/applications/{application}/approve',
        [
            CompetitionApplicationController::class,
            'approve',
        ]
    );

    Route::patch(
        '/admin/applications/{application}/reject',
        [
            CompetitionApplicationController::class,
            'reject',
        ]
    );
});

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin/competitions/{competition}')->group(function () {
        Route::get('/participants', [CompetitionResultController::class, 'participants'])->name('admin.competitions.participants');
        Route::get('/results', [CompetitionResultController::class, 'index'])->name('admin.competitions.results');
        Route::put('/results', [CompetitionResultController::class, 'update'])->name('admin.competitions.results.update');
        Route::post('/results/publish', [CompetitionResultController::class, 'publish'])->name('admin.competitions.results.publish');
        Route::get('/protocol', [CompetitionProtocolController::class, 'preview'])->name('admin.competitions.protocol');
    });

Route::get('/competitions/{competition}/results', [CompetitionResultController::class, 'publicIndex'])->name('competitions.results');
Route::get('/competitions/{competition}/protocol', [CompetitionProtocolController::class, 'download'])->name('competitions.protocol');
Route::get('/athlete/results', [CompetitionResultController::class, 'athleteIndex'])
    ->middleware(['auth', 'verified', 'role:athlete'])->name('athlete.results');

require __DIR__.'/rating.php';
