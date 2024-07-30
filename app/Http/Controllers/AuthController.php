<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\SendVerificationCodeRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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

        return response()->json(['message' => 'Uspješno Ste se odjavili.']);
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
                'message' => 'Prijava nije uspjela.',
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        
        $verified = false;
        if($user->email_verified_at){
            $verified = true;
        }

        return response()->json([
            'verified' => $verified,
            'token' => $token,
        ]);
    }

    protected function SendVerificationCode(SendVerificationCodeRequest $request): JsonResponse
    {
        if($request->email){
            $user = User::where('email', $request->email)->first();
        } else {
            $user = Auth::user();
        }

        if(!$user){
            return response()->json(['message' => 'Korisnik s traženom email adresom ne postoji.'], 404);
        }

        $code = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 6)), 0, 6);

        $user->verification_code = $code;
        $user->verification_code_created_at = now();
        $user->save();

        Mail::to($request->email)->send(new VerificationCodeMail($user->verification_code));
        return response()->json(['message' => 'Verifikacijski kod je poslan na Vašu email adresu.']);
    }

    protected function ResetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if(!$user->verification_code){
            return response()->json(['message' => 'Verifikacijski kod nije validan.'], 403);
        }

        $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $user->verification_code_created_at);
        if($request->code !== $user->verification_code || $created_at->addMinutes(5)->isPast()){
            return response()->json(['message' => 'Verifikacijski kod nije validan.'], 403);
        }

        $user->verification_code = null;
        $user->verification_code_created_at = null;
        $user->password = Hash::make($request->password);
        $user->email_verified_at = now();
        $user->save();

        $credentials = $request->only('email', 'password');
        $token = Auth::attempt($credentials);

        return response()->json(['token' => $token]);
    }
}
