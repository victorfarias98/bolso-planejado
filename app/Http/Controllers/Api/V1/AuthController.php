<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\Plan;
use App\Models\User;
use App\Services\CategoryDefaultsService;
use App\Services\EntitlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, CategoryDefaultsService $categoryDefaults, EntitlementService $entitlements): JsonResponse
    {
        $freePlan = Plan::query()->where('slug', 'free')->first();

        $user = User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'plan_id' => $freePlan?->id,
        ]);

        $categoryDefaults->ensureDefaultsForUser($user);
        $entitlements->syncUserPlanSnapshot($user);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => (new UserResource($user))->resolve(),
            'token' => $token,
            'token_type' => 'Bearer',
        ], Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
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
