<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveRole
{
    public function handle(
        Request $request,
        Closure $next,
        string $role
    ): Response {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Необходима авторизация');
        }

        $activeRole = $request->session()
            ->get('active_role');

        $hasRole = $user->roles()
            ->where('name', $role)
            ->exists();

        if ($activeRole !== $role || !$hasRole) {
            abort(403, 'Нет доступа к этой роли');
        }

        return $next($request);
    }
}