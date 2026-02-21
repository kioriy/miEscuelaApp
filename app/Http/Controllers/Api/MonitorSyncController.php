<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\AuthorizedPerson;

class MonitorSyncController extends Controller
{
    /**
     * Descarga incremental de Alumnos y Autorizados para el Monitor Offline.
     */
    public function pullData(Request $request)
    {
        // 1. Obtenemos la fecha de la última sincronización. 
        // Si es la primera vez (null), ponemos una fecha muy antigua para descargar todo.
        $lastSync = $request->input('last_sync', '2000-01-01 00:00:00');

        // 2. Obtenemos el ID de la escuela.
        // Asumimos que el monitor inició sesión y su token Sanctum tiene el school_id.
        $schoolId = $request->user()->school_id;

        // 3. Buscamos SOLO los alumnos modificados desde el $lastSync
        // Usamos select() para traer solo los campos necesarios y ahorrar RAM en el servidor.
        $students = Student::where('school_id', $schoolId)
            ->where('updated_at', '>', $lastSync)
            ->select('id', 'enrollment_code', 'first_name', 'last_name', 'grade', 'group_letter', 'photo_path', 'photo_hash', 'is_active', 'updated_at')
            ->get();

        // 4. Formateamos las URLs de las fotos de los alumnos
        $students->transform(function ($student) {
            $student->photo_url = $student->photo_path ? asset('storage/' . $student->photo_path) : null;
            return $student;
        });

        // 5. Buscamos a las personas autorizadas relacionadas a esta escuela que hayan cambiado
        // Usamos whereHas para asegurarnos de que solo traemos autorizados de esta escuela específica
        $authorizedPersons = AuthorizedPerson::whereHas('student', function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })
            ->where('updated_at', '>', $lastSync)
            ->select('id', 'student_id', 'full_name', 'relationship', 'photo_path', 'photo_hash', 'is_primary', 'updated_at')
            ->get();

        // 6. Formateamos las URLs de las fotos de los autorizados
        $authorizedPersons->transform(function ($person) {
            $person->photo_url = $person->photo_path ? asset('storage/' . $person->photo_path) : null;
            return $person;
        });

        // 7. Retornamos la información estructurada
        return response()->json([
            'success' => true,
            'timestamp' => now()->toDateTimeString(), // El monitor guardará esta fecha para su próxima petición
            'data' => [
                'students' => $students,
                'authorized_persons' => $authorizedPersons
            ]
        ], 200);
    }
}
