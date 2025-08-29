<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            if (!Auth::guard('web')->attempt($request->only('email', 'password'))) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'data' => null
                ], 401);
            }

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                    'message' => 'Login Berhasil',
                    'data' => [
                        'token' => $token,
                        'user' => $user
                    ]
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'data' => $th->getMessage()
            ], 500);
        }
    }
}
