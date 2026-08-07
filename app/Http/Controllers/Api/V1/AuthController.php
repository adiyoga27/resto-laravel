<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Register new user
     *
     * @group Authentication
     *
     * @unauthenticated
     *
     * @bodyParam name string required User name. Example: John Doe
     * @bodyParam email string required Email address. Example: john@example.com
     * @bodyParam password string required Password (min 8 chars, must be confirmed). Example: password123
     * @bodyParam password_confirmation string required Password confirmation. Example: password123
     * @bodyParam phone string Optional phone number. Example: 08123456789
     *
     * @response 201 {"user":{"id":1,"name":"John Doe","email":"john@example.com","role":"customer"},"token":"1|abc123"}
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'] ?? null,
            'role' => 'customer',
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Login
     *
     * @group Authentication
     *
     * @unauthenticated
     *
     * @bodyParam email string required Email. Example: john@example.com
     * @bodyParam password string required Password. Example: password123
     *
     * @response 200 {"user":{"id":1,"name":"John Doe","email":"john@example.com","role":"customer"},"token":"1|abc123"}
     * @response 401 {"message":"Email atau password salah."}
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Akun Anda dinonaktifkan.'], 403);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout
     *
     * @group Authentication
     *
     * @authenticated
     *
     * @response 200 {"message":"Berhasil logout."}
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil logout.']);
    }
}
