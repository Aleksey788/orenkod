<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\DTO\Auth\RegisterDTO;
use App\DTO\Auth\LoginDTO;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function register(RegisterRequest $request)
    {
        $dto = RegisterDTO::fromArray(
            $request->validated()
        );

        $user = $this->authService->register(
            $dto,
            $request->ip()
        );

        Auth::login($user);

        $request->session()->regenerate();
        $request->session()->put('active_role', 'athlete');

        return (new UserResource($user->load('roles')))
            ->response()
            ->setStatusCode(201);
    }

    public function login(LoginRequest $request)
    {
        $dto = LoginDTO::fromArray(
            $request->validated()
        );

        $user = $this->authService->login($dto);

        $request->session()->regenerate();

        $role = $user->roles()
            ->orderBy('roles.id')
            ->first();

        $request->session()->put(
            'active_role',
            $role?->name
        );

        return new UserResource($user->load('roles'));
    }

    public function me(Request $request)
    {
        return new UserResource(
            $request->user()->load('roles')
        );
    }

    public function switchRole(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', 'string'],
        ]);

        $hasRole = $request->user()
            ->roles()
            ->where('name', $data['role'])
            ->exists();

        abort_unless($hasRole, 403, 'Роль недоступна');

        $request->session()->regenerate();

        $request->session()->put(
            'active_role',
            $data['role']
        );

        return new UserResource(
            $request->user()->load('roles')
        );
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Вы успешно вышли из аккаунта',
        ]);
    }
}