<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HandlesSchoolContext
{
    /**
     * Helper to get the school ID from the request context.
     * Prioritizes 'X-School-Id' header, then falls back to user default.
     */
    protected function getSchoolId(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $request->header('X-School-Id');
        }

        // 1. Check for header (sent by the School Switcher in the frontend)
        $schoolId = $request->header('X-School-Id');

        if ($schoolId) {
            // Validate access if it's a User model
            if ($user instanceof \App\Models\User) {
                if ($user->role === 'super_admin' || $user->hasAccessToSchool($schoolId)) {
                    return $schoolId;
                }
            } else if ($user instanceof \App\Models\Kiosk) {
                // Kiosks are usually locked to their own school_id
                return $user->school_id;
            }
        }

        // 2. Fallback to user's default school_id (works for both User and Kiosk)
        return $user->school_id;
    }
}
