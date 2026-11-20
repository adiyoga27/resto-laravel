<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
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

        ActivityLogger::log('auth.login', 'Login ke sistem (mobile)', $user);

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
        $user = $request->user();

        $request->user()->currentAccessToken()->delete();

        ActivityLogger::log('auth.logout', 'Logout dari sistem (mobile)', $user);

        return response()->json(['message' => 'Berhasil logout.']);
    }
}
