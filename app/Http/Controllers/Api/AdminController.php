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

    public function storeSchool(Request $request)
    {
        $request->validate([
            'cct' => 'required|string|max:50|unique:schools,cct',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'logo_base64' => 'nullable|string',
            'timezone' => 'required|string',
            'isActive' => 'boolean',
            'maxKiosks' => 'required|integer|min:1|max:50'
        ]);

        $logoPath = null;
        if ($request->filled('logo_base64')) {
            $image_parts = explode(";base64,", $request->logo_base64);
            if (count($image_parts) == 2) {
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1] ?? 'png';
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = 'logo_' . time() . '_' . uniqid() . '.' . $image_type;

                \Illuminate\Support\Facades\Storage::disk('public')->put('schools/logos/' . $fileName, $image_base64);
                $logoPath = 'schools/logos/' . $fileName;
            }
        }

        $school = \App\Models\School::create([
            'cct' => $request->cct,
            'name' => $request->name,
            'address' => $request->address,
            'contact_phone' => $request->contact_phone,
            'logo_path' => $logoPath,
            'timezone' => $request->timezone,
            'is_active' => $request->isActive,
        ]);

        // Generar Kioscos automáticamente
        $kiosksQty = $request->maxKiosks;
        for ($i = 0; $i < $kiosksQty; $i++) {
            \App\Models\Kiosk::create([
                'school_id' => $school->id,
                'device_identifier' => 'K-' . strtoupper(substr(uniqid(), -4)) . '-' . mt_rand(10, 99)
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Escuela creada exitosamente.',
            'data' => $school
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

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|in:super_admin,admin,teacher,parent',
            'school_id' => 'nullable|exists:schools,id'
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make('password123'), // Contraseña por defecto
            'role' => $request->role,
        ]);

        // Si es Admin/Director asociarlo a la escuela correspondiente (se necesitará pivot o campo directo si se modifica la BD)
        // Por ahora, como es un solo colegio por director, guardamos el school_id en tabla usuarios si existiese.
        // Simulando envío de invitación

        return response()->json([
            'success' => true,
            'message' => 'Usuario invitado exitosamente.',
            'data' => $user
        ]);
    }
}
