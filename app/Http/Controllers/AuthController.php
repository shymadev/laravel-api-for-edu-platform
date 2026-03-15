<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __construct(
        protected readonly AuthService $authService,
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->toDTO());

            Log::channel('db')->info('User registered', [
                'user_id' => $result['user']->id,
                'action' => 'register',
            ]);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ], 201);

        } catch (\Throwable $e) {
            Log::channel('db')->error('User registration failed', [
                'email' => $request->input('email'),
                'action' => 'register',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Registration failed'], 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->toDTO());

        if ($result === null) {
            Log::channel('db')->warning('Failed login attempt', [
                'email' => $request->input('email'),
                'action' => 'login_failed',
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = $result['user'];

        if ($user->is_blocked) {
            Log::channel('db')->warning('Blocked user login attempt', [
                'user_id' => $user->id,
                'action' => 'login_blocked',
                'ip' => $request->ip(),
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'message' => 'Your account is blocked',
            ], 403);
        }

        if (! is_array($result)) {
            Log::channel('db')->warning('Failed login attempt', [
                'email' => $request->input('email'),
                'action' => 'login_failed',
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        Log::channel('db')->info('User logged in', [
            'user_id' => $result['user']->id,
            'action' => 'login',
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Logged in successfully',
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;

        $this->authService->logout($request->user());

        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->forget('user');

        Log::channel('db')->info('User logged out', [
            'user_id' => $userId,
            'action' => 'logout',
            'ip' => $request->ip(),
        ]);

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function currentUser(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $result = $this->authService->handleGoogleCallback($googleUser);

            Log::channel('db')->info('User logged in via Google', [
                'user_id' => $result['user']->id ?? null,
                'action' => 'login_google',
            ]);

            return redirect(
                "http://tallksy.by/auth/google/callback?token={$result['token']}"
            );

        } catch (\Throwable $e) {
            Log::channel('db')->error('Google auth failed', [
                'action' => 'login_google_failed',
                'exception' => $e,
            ]);

            return redirect('http://tallksy.by/401');
        }
    }
}
