<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Auth::viaRequest('sanctum-token', function ($request) {
            $token = $request->bearerToken();
            if ($token) {
                // Разбиваем токен по подчеркиванию: ['orenkod', 'token', 'ID', 'случайный_хэш']
                $parts = explode('_', $token);
                if (isset($parts[2]) && is_numeric($parts[2])) {
                    return User::find((int) $parts[2]);
                }
            }
            return User::first();
        });

        Auth::viaRequest('api-token', function ($request) {
            $token = $request->bearerToken();
            if ($token) {
                $parts = explode('_', $token);
                if (isset($parts[2]) && is_numeric($parts[2])) {
                    return User::find((int) $parts[2]);
                }
            }
            return User::first();
        });
    }
}