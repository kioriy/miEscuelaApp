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
                    // Crear usuario con rol 'parent'
                    $user = User::create([
                        'name' => $googleUser->getName(),
                        'email' => $email,
                        'password' => Hash::make(Str::random(24)),
                        'role' => 'parent',
                        'google_id' => $googleUser->getId(),
                        'avatar_url' => $googleUser->getAvatar(),
                        'school_id' => $student->school_id, // Keep for backward compatibility/default
                    ]);

                    // Vincular a la escuela en la tabla pivot
                    $user->schools()->attach($student->school_id, ['role' => 'parent']);
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

                // Si tiene school_id pero no está en el pivot (por migraciones manuales o data previa), sincronizar
                if ($user->school_id && !$user->schools()->where('schools.id', $user->school_id)->exists()) {
                    $user->schools()->attach($user->school_id, ['role' => $user->role]);
                }
            }

            // Generar token de acceso (Sanctum)
            $token = $user->createToken('auth_token')->plainTextToken;

            // Cargar escuelas asociadas
            $user->load('schools');

            // Determinar todos los perfiles disponibles para este correo
            $available_profiles = [];
            
            // 1. Rol base en tabla users
            if ($user->role) {
                $available_profiles[] = $user->role;
            }

            // 2. Roles en la tabla pivot de escuelas (ej. un director puede ser teacher en otra)
            foreach ($user->schools as $school) {
                if ($school->pivot && $school->pivot->role && !in_array($school->pivot->role, $available_profiles)) {
                    $available_profiles[] = $school->pivot->role;
                }
            }

            // 3. Verificación de si tiene hijos registrados (rol parent)
            if (!in_array('parent', $available_profiles)) {
                $isTutor = Student::where('tutor_email', $email)
                    ->orWhere('secondary_tutor_email', $email)
                    ->exists();
                if ($isTutor) {
                    $available_profiles[] = 'parent';
                }
            }

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user,
                'schools' => $user->schools,
                'available_profiles' => $available_profiles,
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
