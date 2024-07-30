<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\VerifyEmailRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    public function ChangePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (Hash::check($request->current_password, $user->password)) {
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json(['message' => 'Lozinka uspješno promijenjena.']);
        }
        return response()->json(['message' => 'Pogrešna lozinka.'], 403);
    }

    protected function VerifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $user = Auth::user();

        if(!$user->verification_code){
            return response()->json(['message' => 'Verifikacijski kod nije validan.'], 403);
        }

        $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $user->verification_code_created_at);
        if($request->code !== $user->verification_code || $created_at->addMinutes(5)->isPast()){
            return response()->json(['message' => 'Verifikacijski kod nije validan.'], 403);
        }

        $user->verification_code = null;
        $user->verification_code_created_at = null;
        $user->email_verified_at = now();
        $user->save();

        return response()->json(['message' => 'Email adresa uspješno verifikovana.']);
    }

    protected function DeleteAccount(): JsonResponse
    {
        $user = Auth::user();
        $user->delete();

        return response()->json(['message' => 'Nalog uspješno obrisan.']);
    }
}
