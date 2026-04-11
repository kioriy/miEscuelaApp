<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;

class ImpersonationController extends Controller
{
    /**
     * Start impersonating a user.
     * Only super_admin can impersonate other users.
     */
    public function startImpersonating(Request $request, $id)
    {
        $admin = $request->user();

        // Only super_admin can impersonate
        if ($admin->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para suplantar usuarios.'
            ], 403);
        }

        // Find the target user
        $targetUser = User::with(['school', 'schools'])->find($id);

        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ], 404);
        }

        // Cannot impersonate yourself
        if ($admin->id === $targetUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes suplantarte a ti mismo.'
            ], 422);
        }

        // Cannot impersonate another super_admin
        if ($targetUser->role === 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'No puedes suplantar a otro Super Admin.'
            ], 422);
        }

        // Create a new token for the target user with impersonation metadata
        $token = $targetUser->createToken('impersonation_token', ['impersonated'])->plainTextToken;

        // Determine available profiles for the target user
        $available_profiles = [];
        if ($targetUser->role) {
            $available_profiles[] = $targetUser->role;
        }
        foreach ($targetUser->schools as $school) {
            if ($school->pivot && $school->pivot->role && !in_array($school->pivot->role, $available_profiles)) {
                $available_profiles[] = $school->pivot->role;
            }
        }
        if (!in_array('parent', $available_profiles)) {
            $isTutor = Student::where('tutor_email', $targetUser->email)
                ->orWhere('secondary_tutor_email', $targetUser->email)
                ->exists();
            if ($isTutor) {
                $available_profiles[] = 'parent';
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Ahora estás viendo como ' . $targetUser->name,
            'token' => $token,
            'user' => $targetUser,
            'schools' => $targetUser->schools,
            'available_profiles' => $available_profiles,
            'admin_user' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
            ]
        ]);
    }

    /**
     * Stop impersonating and restore the admin session.
     * This uses the original admin token that was preserved on the frontend.
     */
    public function stopImpersonating(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No hay sesión activa.'
            ], 401);
        }

        // Load the admin user's full data
        $admin = User::with(['school', 'schools'])->find($user->id);

        // Determine available profiles
        $available_profiles = [];
        if ($admin->role) {
            $available_profiles[] = $admin->role;
        }
        foreach ($admin->schools as $school) {
            if ($school->pivot && $school->pivot->role && !in_array($school->pivot->role, $available_profiles)) {
                $available_profiles[] = $school->pivot->role;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Has vuelto a tu cuenta de administrador.',
            'user' => $admin,
            'schools' => $admin->schools,
            'available_profiles' => $available_profiles,
        ]);
    }
}
