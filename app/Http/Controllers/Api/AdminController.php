<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\School;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Shuchkin\SimpleXLSX;

class AdminController extends Controller
{
    public function dashboardStats(Request $request)
    {
        $user = $request->user();
        $isSuperAdmin = $user->role === 'super_admin';
        $schoolId = $user->school_id;

        $querySchools = \App\Models\School::query();
        $queryStudents = \App\Models\Student::query();
        $queryUsers = \App\Models\User::query();
        $queryKiosks = \App\Models\Kiosk::query();

        if (!$isSuperAdmin && $schoolId) {
            $querySchools->where('id', $schoolId);
            $queryStudents->where('school_id', $schoolId);
            $queryUsers->where('school_id', $schoolId);
            $queryKiosks->where('school_id', $schoolId);
        }

        $totalSchools = $querySchools->count();
        $totalStudents = $queryStudents->count();
        $totalUsers = $queryUsers->count();
        $totalKiosks = $queryKiosks->count();

        $directors = (clone $queryUsers)->where('role', 'director')->count();
        $teachers = (clone $queryUsers)->where('role', 'teacher')->count();
        $parents = (clone $queryUsers)->where('role', 'parent')->count();

        $systemHealth = 99.9;

        return response()->json([
            'success' => true,
            'data' => [
                'schools' => $totalSchools,
                'students' => $totalStudents,
                'users' => $totalUsers,
                'kiosks' => $totalKiosks,
                'directors' => $directors,
                'teachers' => $teachers,
                'parents' => $parents,
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
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'address' => $request->address,
            'contact_phone' => $request->contact_phone,
            'logo_path' => $logoPath,
            'timezone' => $request->timezone,
            'is_active' => $request->isActive,
            'allowed_kiosks' => $request->maxKiosks,
        ]);

        // Generar Kioscos automáticamente
        $kiosksQty = $request->maxKiosks;
        for ($i = 0; $i < $kiosksQty; $i++) {
            \App\Models\Kiosk::create([
                'school_id' => $school->id,
                'activation_code' => 'K-' . strtoupper(substr(uniqid(), -4)) . '-' . mt_rand(10, 99)
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

    public function showUser($id)
    {
        $user = \App\Models\User::with('school')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $user]);
    }

    public function updateUser(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|in:super_admin,admin,teacher,parent',
            'school_id' => 'nullable|exists:schools,id'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'school_id' => $request->school_id
        ]);

        return response()->json(['success' => true, 'message' => 'Usuario actualizado exitosamente.', 'data' => $user]);
    }

    public function showSchool($id)
    {
        $school = \App\Models\School::with('kiosks')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $school]);
    }

    public function updateSchool(Request $request, $id)
    {
        $school = \App\Models\School::findOrFail($id);

        $request->validate([
            'cct' => 'required|string|max:50|unique:schools,cct,' . $school->id,
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'logo_base64' => 'nullable|string',
            'timezone' => 'required|string',
            'isActive' => 'boolean',
            'maxKiosks' => 'nullable|integer|min:0'
        ]);

        $logoPath = $school->logo_path;
        if ($request->filled('logo_base64') && strpos($request->logo_base64, 'data:image') === 0) {
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

        $school->update([
            'cct' => $request->cct,
            'name' => $request->name,
            'slug' => $school->name !== $request->name ? \Illuminate\Support\Str::slug($request->name) : $school->slug,
            'address' => $request->address,
            'contact_phone' => $request->contact_phone,
            'logo_path' => $logoPath,
            'timezone' => $request->timezone,
            'is_active' => $request->isActive,
            'allowed_kiosks' => $request->maxKiosks ?? $school->allowed_kiosks,
        ]);

        if ($request->filled('maxKiosks') && $request->maxKiosks > $school->kiosks()->count()) {
            $toAdd = $request->maxKiosks - $school->kiosks()->count();
            for ($i = 0; $i < $toAdd; $i++) {
                \App\Models\Kiosk::create([
                    'school_id' => $school->id,
                    'activation_code' => 'K-' . strtoupper(substr(uniqid(), -4)) . '-' . mt_rand(10, 99)
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Escuela actualizada exitosamente.',
            'data' => $school
        ]);
    }
    public function importStudents(Request $request, $school_id)
    {
        $school = School::findOrFail($school_id);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,json|max:10240',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $data = [];

        try {
            if ($extension === 'json') {
                $content = file_get_contents($file->getRealPath());
                $data = json_decode($content, true);
                if (!is_array($data)) throw new \Exception('Invalid JSON format');
            } elseif ($extension === 'xlsx') {
                if ($xlsx = SimpleXLSX::parse($file->getRealPath())) {
                    $rows = iterator_to_array($xlsx->rows());
                    if (count($rows) > 0) {
                        $headers = array_shift($rows);
                        foreach ($rows as $row) {
                            if (count($headers) === count($row)) {
                                $data[] = array_combine($headers, $row);
                            }
                        }
                    }
                } else {
                    throw new \Exception(SimpleXLSX::parseError());
                }
            } else {
                // CSV
                if (($handle = fopen($file->getRealPath(), 'r')) !== FALSE) {
                    $headers = fgetcsv($handle, 1000, ",");
                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $data[] = array_combine($headers, $row);
                    }
                    fclose($handle);
                }
            }

            if (empty($data)) {
                return response()->json(['success' => false, 'message' => 'El archivo está vacío o tiene un formato inválido.'], 422);
            }

            $results = [
                'total' => count($data),
                'imported' => 0,
                'errors' => [],
                'skipped' => 0
            ];

            // Mapping dictionary (Spanish to DB fields)
            $mapping = [
                'matrícula' => 'enrollment_code',
                'matricula' => 'enrollment_code', // sin acento
                'enrollment_code' => 'enrollment_code',
                'nombre' => 'first_name',
                'first_name' => 'first_name',
                'apellidos' => 'last_name',
                'last_name' => 'last_name',
                'nivel' => 'school_level',
                'school_level' => 'school_level',
                'grado' => 'grade',
                'grade' => 'grade',
                'grupo' => 'group_letter',
                'group_letter' => 'group_letter',
                'turno' => 'shift',
                'shift' => 'shift',
                'email_tutor' => 'tutor_email',
                'tutor_email' => 'tutor_email',
                'email_tutor_2' => 'secondary_tutor_email',
                'secondary_tutor_email' => 'secondary_tutor_email',
            ];

            DB::beginTransaction();

            foreach ($data as $index => $row) {
                // Normalizar llaves
                $normalizedRow = [];
                foreach ($row as $key => $value) {
                    $cleanKey = strtolower(trim($key));
                    if (isset($mapping[$cleanKey])) {
                        $normalizedRow[$mapping[$cleanKey]] = trim($value);
                    }
                }

                $validator = Validator::make($normalizedRow, [
                    'enrollment_code' => 'required|string|max:50',
                    'first_name' => 'required|string|max:100',
                    'last_name' => 'required|string|max:100',
                    'school_level' => 'required|string|max:50',
                    'grade' => 'required|string|max:20',
                    'group_letter' => 'required|string|max:20',
                    'shift' => 'required|in:matutino,vespertino,mixto',
                    'tutor_email' => 'nullable|email|max:150',
                    'secondary_tutor_email' => 'nullable|email|max:150',
                ]);

                if ($validator->fails()) {
                    $results['errors'][] = "Fila " . ($index + 2) . ": " . implode(", ", $validator->errors()->all());
                    continue;
                }

                // Check for duplicates in this school
                $exists = Student::where('school_id', $school->id)
                    ->where('enrollment_code', $normalizedRow['enrollment_code'])
                    ->exists();

                if ($exists) {
                    $results['skipped']++;
                    continue;
                }

                Student::create(array_merge($normalizedRow, ['school_id' => $school->id]));
                $results['imported']++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Proceso de importación finalizado. Alumnos importados: {$results['imported']}.",
                'data' => $results
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStudents(Request $request, $school_id)
    {
        $query = Student::where('school_id', $school_id);

        if ($request->has('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('first_name', 'like', "%$q%")
                    ->orWhere('last_name', 'like', "%$q%")
                    ->orWhere('enrollment_code', 'like', "%$q%");
            });
        }

        if ($request->has('grade') && $request->grade !== 'all') {
            $query->where('grade', $request->grade);
        }

        if ($request->has('group') && $request->group !== 'all') {
            $query->where('group_letter', $request->group);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    public function destroyStudent($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Alumno eliminado correctamente.'
        ]);
    }

    public function getLeaderboard($school_id)
    {
        // Top 5 alumnos con más asistencias hoy
        $today = now()->startOfDay();

        $leaderboard = \App\Models\AttendanceLog::where('school_id', $school_id)
            ->where('scanned_at', '>=', $today)
            ->where('type', 'in')
            ->select('student_id', DB::raw('count(*) as logs_count'))
            ->groupBy('student_id')
            ->orderByDesc('logs_count')
            ->limit(5)
            ->with(['student' => function ($q) {
                $q->select('id', 'first_name', 'last_name', 'enrollment_code', 'grade', 'group_letter');
            }])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $leaderboard
        ]);
    }

    public function directorDashboardStats(Request $request)
    {
        $user = $request->user();
        $schoolId = $user->school_id;

        if (!$schoolId) {
            return response()->json(['success' => false, 'message' => 'No school associated.'], 403);
        }

        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();

        // 1. Basic Counts
        $totalStudents = Student::where('school_id', $schoolId)->count();

        // 2. Attendance Today vs Yesterday
        $attendanceToday = \App\Models\AttendanceLog::where('school_id', $schoolId)
            ->where('scanned_at', '>=', $today)
            ->where('type', 'in')
            ->distinct('student_id')
            ->count();

        $attendanceYesterday = \App\Models\AttendanceLog::where('school_id', $schoolId)
            ->where('scanned_at', '>=', $yesterday)
            ->where('scanned_at', '<', $today)
            ->where('type', 'in')
            ->distinct('student_id')
            ->count();

        $attendanceRate = $totalStudents > 0 ? round(($attendanceToday / $totalStudents) * 100) : 0;
        $prevRate = $totalStudents > 0 ? round(($attendanceYesterday / $totalStudents) * 100) : 0;

        // 3. Entry Summary (Mocking "Late" for now until thresholds exist)
        $lateThreshold = now()->startOfDay()->addHours(8); // 8:00 AM

        $onTime = \App\Models\AttendanceLog::where('school_id', $schoolId)
            ->where('scanned_at', '>=', $today)
            ->where('scanned_at', '<=', $lateThreshold)
            ->where('type', 'in')
            ->distinct('student_id')
            ->count();

        $late = $attendanceToday - $onTime;
        $absent = $totalStudents - $attendanceToday;

        // 4. Attendance by Grade/Group
        $groupStats = Student::where('school_id', $schoolId)
            ->select('grade', 'group_letter', DB::raw('count(*) as total'))
            ->groupBy('grade', 'group_letter')
            ->get();

        foreach ($groupStats as $group) {
            $present = \App\Models\AttendanceLog::where('school_id', $schoolId)
                ->where('scanned_at', '>=', $today)
                ->where('type', 'in')
                ->whereHas('student', function ($q) use ($group) {
                    $q->where('grade', $group->grade)
                        ->where('group_letter', $group->group_letter);
                })
                ->distinct('student_id')
                ->count();

            $group->present = $present;
            $group->percentage = $group->total > 0 ? round(($present / $group->total) * 100, 1) : 0;
        }

        // 5. Unclosed Records Today
        $latestLogsSubToday = \App\Models\AttendanceLog::where('school_id', $schoolId)
            ->whereDate('scanned_at', $today)
            ->select('student_id', DB::raw('MAX(scanned_at) as last_scanned_at'))
            ->groupBy('student_id');

        $unclosedCount = \App\Models\AttendanceLog::joinSub($latestLogsSubToday, 'latest_logs', function ($join) {
            $join->on('attendance_logs.student_id', '=', 'latest_logs.student_id')
                ->on('attendance_logs.scanned_at', '=', 'latest_logs.last_scanned_at');
        })
            ->where('attendance_logs.type', 'in')
            ->count();

        // 6. Staff Status (Teachers)
        $staff = \App\Models\User::where('school_id', $schoolId)
            ->where('role', 'teacher')
            ->take(4)
            ->get();

        // Simulated data for teachers status
        foreach ($staff as $item) {
            $item->status = 'present';
            $item->time = '07:45 AM';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'totalStudents' => $totalStudents,
                'attendanceToday' => $attendanceToday,
                'attendanceRate' => $attendanceRate,
                'attendanceTrend' => $attendanceRate >= $prevRate ? '+' . ($attendanceRate - $prevRate) : '-' . ($prevRate - $attendanceRate),
                'absentCount' => $absent,
                'unclosedCount' => $unclosedCount,
                'staffPresent' => $staff->count(),
                'totalStaff' => \App\Models\User::where('school_id', $schoolId)->where('role', 'teacher')->count(),
                'entrySummary' => [
                    'onTime' => $onTime,
                    'late' => $late,
                    'absent' => $absent
                ],
                'groupStats' => $groupStats,
                'staff' => $staff
            ]
        ]);
    }
    public function getUnclosedAttendance(Request $request)
    {
        $user = $request->user();
        $schoolId = $user->school_id;
        $date = $request->query('date', now()->toDateString());

        if (!$schoolId) {
            return response()->json(['success' => false, 'message' => 'No school associated.'], 403);
        }

        // Subconsulta para encontrar el último log de cada alumno en la fecha dada
        $latestLogsSubquery = \App\Models\AttendanceLog::where('school_id', $schoolId)
            ->whereDate('scanned_at', $date)
            ->select('student_id', DB::raw('MAX(scanned_at) as last_scanned_at'))
            ->groupBy('student_id');

        $unclosedRecords = \App\Models\AttendanceLog::joinSub($latestLogsSubquery, 'latest_logs', function ($join) {
            $join->on('attendance_logs.student_id', '=', 'latest_logs.student_id')
                ->on('attendance_logs.scanned_at', '=', 'latest_logs.last_scanned_at');
        })
            ->where('attendance_logs.type', 'in')
            ->with(['student' => function ($q) {
                $q->select('id', 'first_name', 'last_name', 'enrollment_code', 'grade', 'group_letter');
            }])
            ->get();

        return response()->json([
            'success' => true,
            'date' => $date,
            'data' => $unclosedRecords
        ]);
    }
}
