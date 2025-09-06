<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'user'  => $user,
            'token' => $token
        ], 201);
    }


    public function login(Request $request)
    {
        $start = microtime(true);
        Log::info('Login start');

        $t1 = microtime(true);
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);
        Log::info('Validated', ['took' => microtime(true) - $t1]);

        $t2 = microtime(true);
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::warning('Login failed', ['duration' => microtime(true) - $t2]);
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        Log::info('Password checked', ['took' => microtime(true) - $t2]);

        $t3 = microtime(true);
        $token = JWTAuth::fromUser($user);
        Log::info('JWT created', ['took' => microtime(true) - $t3]);

        Log::info('Login success', ['total_duration' => microtime(true) - $start]);

        return response()->json([
            'user'  => $user,
            'token' => $token
        ]);
    }



    // Get authenticated user
    public function me()
    {
        return response()->json(Auth::user());
    }
}
