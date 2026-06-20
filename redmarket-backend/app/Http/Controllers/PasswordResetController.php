<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Si el email existe, recibirás un enlace de recuperación.'], 200);
        }

        // Generar token de 6 dígitos (más fácil de copiar que un hash largo)
        $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $resetUrl = url("/reset-password?token={$token}&email=" . urlencode($user->email));

        // En dev, devolvemos el link directamente
        $data = [
            'message' => 'Código de recuperación generado.',
        ];

        if (config('app.env') === 'local') {
            $data['reset_url'] = $resetUrl;
            $data['reset_token'] = $token;
        }

        return response()->json($data);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json(['message' => 'Código inválido o expirado.'], 400);
        }

        // Token expira en 60 minutos
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'El código ha expirado. Solicita uno nuevo.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Revocar todos los tokens del usuario (seguridad)
        $user->tokens()->delete();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Contraseña actualizada correctamente. Inicia sesión con tu nueva contraseña.']);
    }
}
