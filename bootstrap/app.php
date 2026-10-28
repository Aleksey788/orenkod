<?php

use App\Http\Middleware\EnsureActiveRole;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureActiveRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, Request $request): ?JsonResponse {
            if (! $request->routeIs(
                'admin.competitions.participants', 'admin.competitions.results*', 'admin.competitions.protocol',
                'competitions.results', 'competitions.protocol', 'athlete.results',
                'ratings.*', 'athlete.rating', 'regional-teams.*', 'admin.rating*',
                'admin.athlete-restrictions.*', 'admin.regional-teams.*',
            ) || $exception instanceof ValidationException || $exception instanceof AuthenticationException) {
                return null;
            }

            $status = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
            $message = match ($status) {
                403 => 'Доступ запрещён.',
                404 => 'Ресурс не найден.',
                419 => 'Срок действия сессии истёк. Обновите CSRF-токен.',
                default => 'Не удалось обработать запрос. Повторите попытку позднее.',
            };

            return response()->json(['message' => $message], $status);
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
