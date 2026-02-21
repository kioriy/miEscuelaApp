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

            $user = User::where('email', $email)->first();

            if (!$user) {
                $studentExists = Student::where('tutor_email', $email)
                    ->orWhere('secondary_tutor_email', $email)
                    ->exists();

                if (!$studentExists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Correo no autorizado. Por favor, acude a la dirección de tu instituto para registrar tu correo: ' . $email
                    ], 403);
                }

                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $email,
                    'google_id' => $googleUser->getId(),
                    'role' => 'parent',
                    'password' => Hash::make(Str::random(24))
                ]);
            } else {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            $apiToken = $user->createToken('parent_mobile_app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'token' => $apiToken,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar la cuenta de Google.',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 401);
        }
    }
}
