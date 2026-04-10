<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;
use Throwable;

#[OA\Tag(name: 'Auth')]
class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/signup',
        summary: 'Register user',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Ahmad'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ahmad10@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: '12345678'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: '12345678'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'User registered'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function signup(SignupRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            if (! $user) {
                throw new \RuntimeException('User registration failed.');
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'User registered successfully',
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Database error while registering user.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong during signup.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    #[OA\Post(
        path: '/api/login',
        summary: 'Login user',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ahmad10@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: '12345678'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login successful'),
            new OA\Response(response: 401, description: 'Invalid credentials'),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->validated();

            if (! Auth::attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            $user = User::where('email', $credentials['email'])->first();

            if (! $user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong during login.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    #[OA\Post(
        path: '/api/logout',
        summary: 'Logout user',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Logout successful'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function logout(): JsonResponse
    {
        try {
            $user = request()->user();

            if (! $user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $token = $user->currentAccessToken();

            if (! $token) {
                return response()->json([
                    'status' => false,
                    'message' => 'No active token found',
                ], 400);
            }

            $token->delete();

            return response()->json([
                'status' => true,
                'message' => 'Logout successful',
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong during logout.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
