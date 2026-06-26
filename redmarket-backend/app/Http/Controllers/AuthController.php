<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $throttleKey = 'register:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'message' => "Demasiados registros desde esta IP. Intenta de nuevo en {$seconds} segundos.",
            ], 429);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'latitud' => $request->latitud,
            'longitud' => $request->longitud,
        ]);

        $user->assignRole('Cliente');

        RateLimiter::clear($throttleKey);

        $expiresAt = $request->boolean('remember_me') ? now()->addDays(30) : now()->addHours(2);
        $token = $user->createToken('auth_token', expiresAt: $expiresAt)->plainTextToken;

        return response()->json([
            'user' => $user->load('roles'),
            'token' => $token,
            'expires_at' => $expiresAt,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'message' => "Demasiados intentos. Intenta de nuevo en {$seconds} segundos.",
                'retry_after' => $seconds,
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            return response()->json(['message' => 'Las credenciales son incorrectas.'], 401);
        }

        RateLimiter::clear($throttleKey);

        try {
            // Opcional: revocar tokens anteriores si no multi-sesión
            if ($request->boolean('revoke_others')) {
                $user->tokens()->delete();
            }

            $expiresAt = $request->boolean('remember_me') ? now()->addDays(30) : now()->addHours(2);
            $token = $user->createToken('auth_token', expiresAt: $expiresAt)->plainTextToken;

            return response()->json([
                'user' => $user->load('roles'),
                'token' => $token,
                'expires_at' => $expiresAt,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Login error: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno del servidor'], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Sesión cerrada en todos los dispositivos.']);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('roles'),
        ]);
    }

    public function updatePerfil(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
        ]);

        if ($request->filled('name')) {
            $user->name = $request->name;
        }
        if ($request->has('telefono')) {
            $user->telefono = $request->telefono;
        }
        if ($request->has('direccion')) {
            $user->direccion = $request->direccion;
        }
        if ($request->has('latitud')) {
            $user->latitud = $request->latitud;
        }
        if ($request->has('longitud')) {
            $user->longitud = $request->longitud;
        }

        $user->save();

        return response()->json([
            'user' => $user->load('roles'),
            'message' => 'Perfil actualizado correctamente'
        ]);
    }
}
