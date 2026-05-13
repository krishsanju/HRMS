<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        $user = Auth::user();
        // Assuming 'admin' is a role or a specific user type for HR Admin Panel
        // You might want to add a check here if only specific roles can log into the admin panel
        // if ($user->role !== 'admin') {
        //     Auth::logout();
        //     throw ValidationException::withMessages([
        //         'email' => ['You do not have administrative access.'],
        //     ]);
        // }

        $token = $user->createToken('auth_token', ['admin'])->plainTextToken; // 'admin' is a scope

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user, // Return user data, including role if available
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}