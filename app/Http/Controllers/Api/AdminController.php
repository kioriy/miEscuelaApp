<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\School;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Shuchkin\SimpleXLSX;
use App\Traits\HandlesSchoolContext;

class AdminController extends Controller
{
    use HandlesSchoolContext;

    public function dashboardStats(Request $request)
    {
        $user = $request->user();
        $isSuperAdmin = $user->role === 'super_admin';
        $schoolId = $this->getSchoolId($request);

        $querySchools = \App\Models\School::query();
        $queryStudents = \App\Models\Student::query();
        $queryUsers = \App\Models\User::query();
        $queryKiosks = \App\Models\Kiosk::query();

        if (!$isSuperAdmin && $schoolId) {
            $querySchools->where('id', $schoolId);
            $queryStudents->where('school_id', $schoolId);
            // Optimization: if we have a school context, we filter users that belong to that school
            $queryUsers->whereHas('schools', function ($q) use ($schoolId) {
                $q->where('schools.id', $schoolId);
            })->orWhere('school_id', $schoolId); // legacy fallback
            $queryKiosks->where('school_id', $schoolId);
        } elseif ($isSuperAdmin && $schoolId) {
            // Super Admin can filter by a specific school if they want
            $querySchools->where('id', $schoolId);
            $queryStudents->where('school_id', $schoolId);
            $queryUsers->whereHas('schools', function ($q) use ($schoolId) {
                $q->where('schools.id', $schoolId);
            })->orWhere('school_id', $schoolId);
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
        $user = $request->user();

        if ($user->role === 'super_admin') {
            $schools = \App\Models\School::withCount('ownedKiosks as kiosks_count')->get();
        } else {
            $schools = $user->schools()->withCount('ownedKiosks as kiosks_count')->get();
        }

        // Contar el número aproximado de alumnos manualmente
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
            'cct' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'logo_base64' => 'nullable|string',
            'timezone' => 'required|string',
            'isActive' => 'boolean',
            'maxKiosks' => 'required|integer|min:1|max:50',
            'entry_time' => 'nullable|string',
            'tolerance_minutes' => 'nullable|integer|min:0'
        ]);

        $logoPath = null;
        if ($request->filled('logo_base64')) {
            $image_parts = explode(";base64,", $request->logo_base64);
            if (count($image_parts) == 2) {
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1] ?? 'png';
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = 'logo_' . time() . '_' . uniqid() . '.' . $image_type;

                Storage::disk('public')->put('schools/logos/' . $fileName, $image_base64);
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
            'entry_time' => $request->entry_time,
            'tolerance_minutes' => $request->tolerance_minutes ?? 15,
        ]);

        // Generar Kioscos automáticamente
        $kiosksQty = $request->maxKiosks;
        for ($i = 0; $i < $kiosksQty; $i++) {
            $kiosk = \App\Models\Kiosk::create([
                'owner_school_id' => $school->id,
                'activation_code' => 'K-' . strtoupper(substr(uniqid(), -4)) . '-' . mt_rand(10, 99)
            ]);

            // Adjuntar inmediatamente la escuela a la tabla pivote de kioscos
            $kiosk->schools()->attach($school->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Escuela creada exitosamente.',
            'data' => $school
        ]);
    }

    public function getUsers(Request $request)
    {
        $schoolId = $this->getSchoolId($request);

        if ($schoolId) {
            $users = \App\Models\User::whereHas('schools', function ($q) use ($schoolId) {
                $q->where('schools.id', $schoolId);
            })->orWhere('school_id', $schoolId)->with('schools')->get();
        } else {
            $users = \App\Models\User::with('schools')->get();
        }

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
            'role' => 'required|string|in:super_admin,admin,teacher,parent,director',
            'school_ids' => 'nullable|array',
            'school_ids.*' => 'exists:schools,id'
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make('password123'), // Contraseña por defecto
            'role' => $request->role,
            'school_id' => !empty($request->school_ids) ? $request->school_ids[0] : null, // fallback
        ]);

        if (!empty($request->school_ids)) {
            $user->schools()->attach($request->school_ids, ['role' => $request->role]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario invitado exitosamente.',
            'data' => $user->load('schools')
        ]);
    }

    public function showUser($id)
    {
        $user = \App\Models\User::with(['school', 'schools'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $user]);
    }

    public function updateUser(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|in:super_admin,admin,teacher,parent,director',
            'school_ids' => 'nullable|array',
            'school_ids.*' => 'exists:schools,id'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'school_id' => !empty($request->school_ids) ? $request->school_ids[0] : null
        ]);

        if (is_array($request->school_ids)) {
            $user->schools()->syncWithPivotValues($request->school_ids, ['role' => $request->role]);
        } else {
            $user->schools()->detach();
        }

        return response()->json(['success' => true, 'message' => 'Usuario actualizado exitosamente.', 'data' => $user->load('schools')]);
    }

    public function showSchool($id)
    {
        $school = \App\Models\School::with('ownedKiosks', 'activeKiosks')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $school]);
    }

    public function updateSchool(Request $request, $id)
    {
        $school = \App\Models\School::findOrFail($id);

        $request->validate([
            'cct' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'logo_base64' => 'nullable|string',
            'timezone' => 'required|string',
            'isActive' => 'boolean',
            'maxKiosks' => 'nullable|integer|min:0',
            'entry_time' => 'nullable|string',
            'tolerance_minutes' => 'nullable|integer|min:0'
        ]);

        $logoPath = $school->logo_path;
        if ($request->filled('logo_base64') && strpos($request->logo_base64, 'data:image') === 0) {
            $image_parts = explode(";base64,", $request->logo_base64);
            if (count($image_parts) == 2) {
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1] ?? 'png';
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = 'logo_' . time() . '_' . uniqid() . '.' . $image_type;
                Storage::disk('public')->put('schools/logos/' . $fileName, $image_base64);
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
            'entry_time' => $request->has('entry_time') ? $request->entry_time : $school->entry_time,
            'tolerance_minutes' => $request->has('tolerance_minutes') ? $request->tolerance_minutes : $school->tolerance_minutes,
        ]);

        if ($request->filled('maxKiosks') && $request->maxKiosks > $school->ownedKiosks()->count()) {
            $toAdd = $request->maxKiosks - $school->ownedKiosks()->count();
            for ($i = 0; $i < $toAdd; $i++) {
                $kiosk = \App\Models\Kiosk::create([
                    'owner_school_id' => $school->id,
                    'activation_code' => 'K-' . strtoupper(substr(uniqid(), -4)) . '-' . mt_rand(10, 99)
                ]);

                $kiosk->schools()->attach($school->id);
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
                        $val = trim($value);
                        if ($mapping[$cleanKey] === 'shift') {
                            $val = strtolower($val);
                        }
                        $normalizedRow[$mapping[$cleanKey]] = $val;
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
                    $results['errors'][] = "Fila " . ($index + 2) . ": La matrícula '{$normalizedRow['enrollment_code']}' ya está registrada en esta escuela.";
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

    public function getLeaderboard(Request $request, $school_id = null)
    {
        $schoolId = $school_id ?: $this->getSchoolId($request);
        // Top 5 alumnos con más asistencias hoy
        $localStart = $request->query('local_start');
        $today = $localStart ? \Illuminate\Support\Carbon::parse($localStart)->toDateTimeString() : now()->startOfDay();

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
        $schoolId = $this->getSchoolId($request);

        if (!$schoolId) {
            return response()->json(['success' => false, 'message' => 'No se ha seleccionado una escuela.'], 403);
        }

        $localStart = $request->query('local_start');
        $today = $localStart ? \Illuminate\Support\Carbon::parse($localStart)->toDateTimeString() : now()->startOfDay();
        $yesterday = \Illuminate\Support\Carbon::parse($today)->subDay()->toDateTimeString();

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
        $lateThreshold = \Illuminate\Support\Carbon::parse($today)->addHours(8); // 8:00 AM

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
            ->where('scanned_at', '>=', $today)
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
        $school_id = $request->user()->school_id;
        $localStart = $request->query('local_start');

        // Si no mandan local_start, intentamos deducirlo solo por fecha para el subquery
        $todayDate = $localStart ? \Illuminate\Support\Carbon::parse($localStart)->toDateString() : now()->toDateString();
        $todayDateTime = $localStart ? \Illuminate\Support\Carbon::parse($localStart)->toDateTimeString() : now()->startOfDay()->toDateTimeString();

        $latestLogsSubquery = \App\Models\AttendanceLog::select('student_id', DB::raw('MAX(scanned_at) as last_scanned_at'))
            ->where('school_id', $school_id)
            ->where('scanned_at', '>=', $todayDateTime)
            ->groupBy('student_id');

        $unclosedRecords = \App\Models\AttendanceLog::joinSub($latestLogsSubquery, 'latest_logs', function ($join) {
            $join->on('attendance_logs.student_id', '=', 'latest_logs.student_id')
                ->on('attendance_logs.scanned_at', '=', 'latest_logs.last_scanned_at');
        })
            ->with('student')
            ->where('attendance_logs.type', 'in')
            ->where('attendance_logs.school_id', $school_id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $unclosedRecords
        ]);
    }

    /**
     * Gestión de Estudiantes y Fotos
     */

    public function getStudents(Request $request)
    {
        $schoolId = $this->getSchoolId($request);
        $query = \App\Models\Student::where('school_id', $schoolId);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('enrollment_code', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('last_name')->get();

        // Transform to include absolute photo URL
        $students->transform(function ($student) {
            $student->photo_url = $student->photo_path ? asset('storage/' . $student->photo_path) : null;
            return $student;
        });

        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }

    public function storeStudent(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'enrollment_code' => 'required|string|unique:students,enrollment_code,NULL,id,school_id,' . $request->user()->school_id,
            'school_level' => 'required|string',
            'grade' => 'required|string',
            'group_letter' => 'required|string',
        ]);

        $data = $request->except(['photo_url', 'photo']);
        $data['school_id'] = $this->getSchoolId($request);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('students/photos', 'public');
            $data['photo_path'] = $path;
            $data['photo_hash'] = md5_file($request->file('photo')->getRealPath());
        }

        $student = \App\Models\Student::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Estudiante creado correctamente.',
            'data' => $student
        ]);
    }

    public function showStudent(Request $request, $id)
    {
        $student = \App\Models\Student::where('school_id', $this->getSchoolId($request))->findOrFail($id);
        $student->photo_url = $student->photo_path ? asset('storage/' . $student->photo_path) : null;

        return response()->json([
            'success' => true,
            'data' => $student
        ]);
    }

    public function updateStudent(Request $request, $id)
    {
        $student = \App\Models\Student::where('school_id', $this->getSchoolId($request))->findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'enrollment_code' => 'required|string|unique:students,enrollment_code,' . $id . ',id,school_id,' . $request->user()->school_id,
        ]);

        $data = $request->except(['photo_url', 'photo']);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($student->photo_path) {
                Storage::disk('public')->delete($student->photo_path);
            }
            $path = $request->file('photo')->store('students/photos', 'public');
            $data['photo_path'] = $path;
            $data['photo_hash'] = md5_file($request->file('photo')->getRealPath());
        }

        $student->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Estudiante actualizado correctamente.',
            'data' => $student
        ]);
    }

    public function destroyStudent(Request $request, $id)
    {
        $student = \App\Models\Student::where('school_id', $this->getSchoolId($request))->findOrFail($id);

        if ($student->photo_path) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Estudiante eliminado.'
        ]);
    }

    /**
     * Carga Masiva de Fotos desde ZIP
     * Matching por nombre normalizado: nombre_apellido.jpg
     */
    public function bulkUploadPhotos(Request $request)
    {
        Log::info('Bulk Upload Started', [
            'has_file' => $request->hasFile('zip_file'),
            'content_length' => $request->headers->get('content-length'),
            'all_params' => $request->all(),
            'files' => $_FILES
        ]);

        // Detectar si el archivo excedió los límites de PHP (upload_max_filesize / post_max_size)
        if (!$request->hasFile('zip_file') && count($request->all()) === 0 && $request->headers->get('content-length') > 0) {
            Log::error('Bulk Upload Failed: File exceeds PHP limits (detected by content-length)');
            return response()->json([
                'success' => false,
                'message' => 'El archivo ZIP es demasiado grande para la configuración actual de PHP. Por favor aumenta upload_max_filesize y post_max_size en tu php.ini.'
            ], 422);
        }

        // Usar Validator manualmente para capturar y devolver el error exacto
        $validator = Validator::make($request->all(), [
            'zip_file' => 'required|file|max:102400', // Subido a 100MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', $validator->errors()->all()),
                'debug' => [
                    'has_file' => $request->hasFile('zip_file'),
                    'file_size' => $request->hasFile('zip_file') ? $request->file('zip_file')->getSize() : 0,
                    'mime' => $request->hasFile('zip_file') ? $request->file('zip_file')->getMimeType() : 'none'
                ]
            ], 422);
        }

        // Validación extra de extensión para ser menos estrictos con MIME pero seguros con el archivo
        $zipFile = $request->file('zip_file');
        if (!in_array(strtolower($zipFile->getClientOriginalExtension()), ['zip'])) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo debe tener extensión .zip'
            ], 422);
        }

        $school_id = $this->getSchoolId($request);
        $zipFile = $request->file('zip_file');

        $tempPath = storage_path('app/temp/bulk_' . uniqid());
        mkdir($tempPath, 0777, true);

        $zip = new \ZipArchive;
        if ($zip->open($zipFile->getRealPath()) === TRUE) {
            $zip->extractTo($tempPath);
            $zip->close();
        } else {
            return response()->json(['success' => false, 'message' => 'No se pudo abrir el archivo ZIP.'], 422);
        }

        $files = scandir($tempPath);
        $successCount = 0;
        $errors = [];

        // Obtener todos los alumnos de la escuela para cachear nombres
        $students = \App\Models\Student::where('school_id', $school_id)->get();

        foreach ($files as $filename) {
            if ($filename === '.' || $filename === '..' || is_dir($tempPath . '/' . $filename)) continue;

            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) continue;

            // Normalizar el nombre del archivo (quitar extensión y normalizar texto)
            $nameInFile = $this->normalizeString(pathinfo($filename, PATHINFO_FILENAME));

            $matchedStudent = $students->first(function ($s) use ($nameInFile) {
                $fullName = $this->normalizeString($s->first_name . ' ' . $s->last_name);
                return $fullName === $nameInFile;
            });

            if ($matchedStudent) {
                $newPath = 'students/photos/' . uniqid() . '.' . $extension;
                Storage::disk('public')->put($newPath, file_get_contents($tempPath . '/' . $filename));

                // Borrar foto anterior
                if ($matchedStudent->photo_path) {
                    Storage::disk('public')->delete($matchedStudent->photo_path);
                }

                $matchedStudent->update([
                    'photo_path' => $newPath,
                    'photo_hash' => md5_file($tempPath . '/' . $filename)
                ]);
                $successCount++;
            } else {
                $errors[] = $filename . " (No se encontró coincidencia con el nombre)";
            }
        }

        // Limpiar temp
        $this->recursiveDelete($tempPath);

        return response()->json([
            'success' => true,
            'data' => [
                'success' => $successCount,
                'errors' => $errors
            ]
        ]);
    }

    private function normalizeString($str)
    {
        $str = strtolower($str);
        $str = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
            $str
        );
        // Reemplazar cualquier cosa que no sea letras o números por un espacio
        $str = preg_replace('/[^a-z0-9]/', ' ', $str);
        // Colapsar espacios múltiples y limpiar
        $str = preg_replace('/\s+/', ' ', $str);
        return trim($str);
    }

    private function recursiveDelete($dir)
    {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!$this->recursiveDelete($dir . DIRECTORY_SEPARATOR . $item)) return false;
        }
        return rmdir($dir);
    }
}
