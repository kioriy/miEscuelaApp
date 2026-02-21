<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboardStats(Request $request)
    {
        // En un futuro se puede filtrar si es Super Admin (todo) o Director (solo su escuela)
        $totalSchools = \App\Models\School::count();
        $totalStudents = \App\Models\Student::count();
        $totalUsers = \App\Models\User::count();
        $totalKiosks = \App\Models\Kiosk::count();

        // Puedes calcular el estatus del sistema (ej: % de escuelas activas o kioscos)
        $systemHealth = 99.9; // Hardcodeado por ahora como demostración

        return response()->json([
            'success' => true,
            'data' => [
                'schools' => $totalSchools,
                'students' => $totalStudents,
                'users' => $totalUsers,
                'kiosks' => $totalKiosks,
                'systemHealth' => $systemHealth
            ]
        ]);
    }

    public function getSchools(Request $request)
    {
        // En Producción se añadiría paginación
        $schools = \App\Models\School::withCount('kiosks')->get();
        // Contar el número aproximado de alumnos manualmente agrupando por si acaso
        foreach ($schools as $school) {
            $school->students_count = \App\Models\Student::where('school_id', $school->id)->count();
        }

        return response()->json([
            'success' => true,
            'data' => $schools
        ]);
    }

    public function getUsers(Request $request)
    {
        $users = \App\Models\User::with('school')->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }
}
