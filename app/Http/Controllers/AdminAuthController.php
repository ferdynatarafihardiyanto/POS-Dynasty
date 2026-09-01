<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json(['message' => 'Email atau password salah'], 401);
            }

            return response()->json([
                'message' => 'Login admin berhasil',
                'data' => [
                    'admin' => $user,
                    'token' => $user->createToken('admin-token')->plainTextToken
                ]
            ]);
        }

        return response()->json(['message' => 'Email atau password salah'], 401);
    }

    public function me(Request $request)
    {
        return response()->json([
            'message' => 'Data admin berhasil diambil',
            'data' => $request->user()
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }
}
