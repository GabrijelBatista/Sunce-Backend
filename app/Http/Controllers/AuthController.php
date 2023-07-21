<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function Login(LoginRequest $request): JsonResponse
    {
        return $this->RespondWithToken($request);
    }

    public function Register(RegisterRequest $request): JsonResponse
    {
        User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return $this->RespondWithToken($request);
    }

    public function Logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function Refresh(): JsonResponse
    {
        return $this->RespondWithToken(auth()->refresh());
    }

    protected function RespondWithToken(RegisterRequest|LoginRequest|string $request): JsonResponse
    {
        if (!is_string($request)) {
            $credentials = $request->only('email', 'password');

            $token = Auth::attempt($credentials);
        } else {
            $token = $request;
        }

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }
}
