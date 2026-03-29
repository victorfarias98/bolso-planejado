<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\CategoryDefaultsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, CategoryDefaultsService $categoryDefaults): JsonResponse
    {
        $user = User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            // Temporariamente sem plano/assinatura
            'plan_id' => null,
        ]);

        $categoryDefaults->ensureDefaultsForUser($user);
        // Temporariamente desativado enquanto billing/subscriptions estiverem off

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => (new UserResource($user))->resolve(),
            'token' => $token,
            'token_type' => 'Bearer',
        ], Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $email = $request->validated('email');
        $password = $request->validated('password');

        // Logins hardcoded temporários (admin e demo)
        $adminEmail = env('ADMIN_EMAIL', 'admin@bolsoplanejado.local');
        $adminPassword = env('ADMIN_PASSWORD', 'admin123');
        $demoEmail = env('DEMO_EMAIL', 'demo@bolsoplanejado.local');
        $demoPassword = env('DEMO_PASSWORD', 'demo123');

        if ($email === $adminEmail && hash_equals($adminPassword, $password)) {
            $user = User::query()->firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Admin',
                    'password' => bcrypt($adminPassword),
                    'is_admin' => true,
                ],
            );
        } elseif ($email === $demoEmail && hash_equals($demoPassword, $password)) {
            $user = User::query()->firstOrCreate(
                ['email' => $demoEmail],
                [
                    'name' => 'Demo',
                    'password' => bcrypt($demoPassword),
                    'is_admin' => false,
                ],
            );
        } else {
            $user = User::query()->where('email', $email)->first();
        }

        if (! $user || ! Hash::check($password, $user->password)) {
            return response()->json([
                'message' => 'Credenciais inválidas.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => (new UserResource($user))->resolve(),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function me(Request $request): JsonResponse
    {
        return (new UserResource($request->user()))->response();
    }
}
