<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\JsonResponse;
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

            return response()->json(['message' => 'Password changed successfully']);
        }
        return response()->json(['message' => 'Password incorrect'], 403);
    }
}
