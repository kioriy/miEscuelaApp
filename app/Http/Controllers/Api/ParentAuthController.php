<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ParentAuthController extends Controller
{
    /**
     * Recibe el token de Google desde Ionic/Vue y valida el acceso.
     */
    public function loginWithGoogle(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($request->token);
            $email = $googleUser->getEmail();

            // 1. Buscar usuario existente
            $user = User::where('email', $email)->first();

            if (!$user) {
                // 2. Si no existe, verificar si es un tutor de un alumno para auto-registro
                $student = Student::where('tutor_email', $email)
                    ->orWhere('secondary_tutor_email', $email)
                    ->first();

                if ($student) {
                    // Crear usuario con rol 'parent' vinculado a la escuela del alumno
                    $user = User::create([
                        'name' => $googleUser->getName(),
                        'email' => $email,
                        'password' => Hash::make(Str::random(24)),
                        'role' => 'parent',
                        'google_id' => $googleUser->getId(),
                        'avatar_url' => $googleUser->getAvatar(),
                        'school_id' => $student->school_id,
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Esta cuenta de Google no está asociada a ningún usuario o tutor registrado en el sistema.'
                    ], 403);
                }
            } else {
                // Actualizar información de Google en el usuario existente
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar_url' => $googleUser->getAvatar(),
                ]);
            }

            // Generar token de acceso (Sanctum)
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user,
                'message' => 'Login exitoso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar la cuenta de Google.',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}
