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
        
        // Check if the frontend is requesting global context
        $isGlobal = $request->query('global') === 'true';
        $schoolId = $isGlobal ? null : $this->getSchoolId($request);

        $querySchools = \App\Models\School::query();
        $queryStudents = \App\Models\Student::query();
        $queryUsers = \App\Models\User::query();
        $queryKiosks = \App\Models\Kiosk::query();

        if (!$isSuperAdmin && $schoolId) {
            $querySchools->where('id', $schoolId);
            $queryStudents->where('school_id', $schoolId);
            // Optimization: if we have a school context, we filter users that belong to that school
            $queryUsers->where(function($query) use ($schoolId) {
                $query->whereHas('schools', function ($q) use ($schoolId) {
                    $q->where('schools.id', $schoolId);
                })->orWhere('school_id', $schoolId); // legacy fallback
            });
            $queryKiosks->where('owner_school_id', $schoolId);
        } elseif ($isSuperAdmin && $schoolId) {
            // Super Admin can filter by a specific school if they want
            $querySchools->where('id', $schoolId);
            $queryStudents->where('school_id', $schoolId);
            $queryUsers->where(function($query) use ($schoolId) {
                $query->whereHas('schools', function ($q) use ($schoolId) {
                    $q->where('schools.id', $schoolId);
                })->orWhere('school_id', $schoolId);
            });
            $queryKiosks->where('owner_school_id', $schoolId);
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
            $school->users_count = \App\Models\User::where(function($query) use ($school) {
                $query->whereHas('schools', function ($q) use ($school) {
                    $q->where('schools.id', $school->id);
                })->orWhere('school_id', $school->id);
            })->count();
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
            'tolerance_minutes' => 'nullable|integer|min:0',
            'qr_scan_type' => 'required|in:raw_id,hash_split,query_param,sep_url'
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
            'qr_scan_type' => $request->qr_scan_type,
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

        $query = \App\Models\User::with(['school', 'schools']);

        // Filtro por contexto de escuela (X-School-Id header)
        if ($schoolId) {
            $query->where(function ($q) use ($schoolId) {
                $q->whereHas('schools', function ($sub) use ($schoolId) {
                    $sub->where('schools.id', $schoolId);
                })->orWhere('school_id', $schoolId);
            });
        }

        // Filtro por búsqueda (nombre o email)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por rol
        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        // Filtro por escuela específica (distinto al contexto global X-School-Id)
        if ($request->filled('school_id')) {
            $filterSchoolId = $request->input('school_id');
            $query->where(function ($q) use ($filterSchoolId) {
                $q->whereHas('schools', function ($sub) use ($filterSchoolId) {
                    $sub->where('schools.id', $filterSchoolId);
                })->orWhere('school_id', $filterSchoolId);
            });
        }

        $users = $query->orderBy('name')->get();

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

    public function destroyUser($id)
    {
        $user = \App\Models\User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'Usuario eliminado correctamente']);
    }

    public function resendWelcomeEmail($id)
    {
        $user = \App\Models\User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
        }

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\WelcomeUserMail($user));
            return response()->json(['success' => true, 'message' => 'Correo de bienvenida reenviado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al enviar el correo: ' . $e->getMessage()], 500);
        }
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
        
        // Cargar numeralia para la vista de detalle
        $school->students_count = \App\Models\Student::where('school_id', $school->id)->count();
        $school->users_count = \App\Models\User::where(function($query) use ($school) {
            $query->whereHas('schools', function($q) use ($school) {
                $q->where('schools.id', $school->id);
            })->orWhere('school_id', $school->id);
        })->count();
        $school->kiosks_count = $school->ownedKiosks()->count();
        $school->groups_count = \App\Models\Classroom::where('school_id', $school->id)->count();

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
            'tolerance_minutes' => 'nullable|integer|min:0',
            'qr_scan_type' => 'required|in:raw_id,hash_split,query_param,sep_url'
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
            'qr_scan_type' => $request->qr_scan_type,
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

                $classroom = \App\Models\Classroom::firstOrCreate([
                    'school_id' => $school->id,
                    'school_level' => $normalizedRow['school_level'],
                    'grade' => $normalizedRow['grade'],
                    'group_letter' => $normalizedRow['group_letter'],
                    'shift' => $normalizedRow['shift'],
                ]);

                $studentData = array_diff_key($normalizedRow, array_flip(['school_level', 'grade', 'group_letter', 'shift']));
                $studentData['school_id'] = $school->id;
                $studentData['classroom_id'] = $classroom->id;

                Student::create($studentData);
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

        $school = \App\Models\School::find($schoolId);
        $timezone = $school && $school->timezone ? $school->timezone : 'America/Mexico_City';

        // Top 5 alumnos con más asistencias hoy
        $localStart = $request->query('local_start');

        if ($localStart) {
            $today = \Illuminate\Support\Carbon::parse(substr($localStart, 0, 10), $timezone)->startOfDay()->toDateTimeString();
        } else {
            $today = now($timezone)->startOfDay()->toDateTimeString();
        }

        $leaderboard = \App\Models\AttendanceLog::where('school_id', $schoolId)
            ->where('scanned_at', '>=', $today)
            ->where('type', 'in')
            ->select('student_id', DB::raw('count(*) as logs_count'))
            ->groupBy('student_id')
            ->orderByDesc('logs_count')
            ->limit(5)
            ->with(['student' => function ($q) {
                $q->select('id', 'first_name', 'last_name', 'enrollment_code', 'classroom_id');
            }, 'student.classroom'])
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

        $school = \App\Models\School::find($schoolId);
        $timezone = $school && $school->timezone ? $school->timezone : 'America/Mexico_City';

        $localStart = $request->query('local_start');
        // Fix: JS toISOString() sends UTC. Use it just for the date part, or default to backend's startOfDay.
        if ($localStart) {
            $localTodayStart = \Illuminate\Support\Carbon::parse(substr($localStart, 0, 10), $timezone)->startOfDay();
        } else {
            $localTodayStart = now($timezone)->startOfDay();
        }

        $today = $localTodayStart->copy()->setTimezone('UTC');
        $yesterday = $localTodayStart->copy()->subDay()->setTimezone('UTC');
        $tomorrow = $localTodayStart->copy()->addDay()->setTimezone('UTC');

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

        // 3. Entry Summary
        $onTime = \App\Models\AttendanceLog::where('school_id', $schoolId)
            ->where('scanned_at', '>=', $today)
            ->where('scanned_at', '<', $tomorrow)
            ->where('type', 'in')
            ->where('status', 'present')
            ->distinct('student_id')
            ->count();

        $late = \App\Models\AttendanceLog::where('school_id', $schoolId)
            ->where('scanned_at', '>=', $today)
            ->where('scanned_at', '<', $tomorrow)
            ->where('type', 'in')
            ->where('status', 'late')
            ->distinct('student_id')
            ->count();

        $absent = $totalStudents - ($onTime + $late);

        // 4. Attendance by Grade/Group
        $groupStats = \App\Models\Classroom::where('school_id', $schoolId)
            ->withCount('students as total')
            ->having('total', '>', 0)
            ->get();

        foreach ($groupStats as $group) {
            $present = \App\Models\AttendanceLog::where('school_id', $schoolId)
                ->where('scanned_at', '>=', $today)
                ->where('scanned_at', '<', $tomorrow)
                ->where('type', 'in')
                ->whereHas('student', function ($q) use ($group) {
                    $q->where('classroom_id', $group->id);
                })
                ->distinct('student_id')
                ->count();

            $group->present = $present;
            $group->percentage = $group->total > 0 ? round(($present / $group->total) * 100, 1) : 0;
        }

        // 5. Unclosed Records Today
        $latestLogsSubToday = \App\Models\AttendanceLog::where('school_id', $schoolId)
            ->where('scanned_at', '>=', $today)
            ->where('scanned_at', '<', $tomorrow)
            ->select('student_id', DB::raw('MAX(scanned_at) as last_scanned_at'))
            ->groupBy('student_id');

        $unclosedCount = \App\Models\AttendanceLog::joinSub($latestLogsSubToday, 'latest_logs', function ($join) {
            $join->on('attendance_logs.student_id', '=', 'latest_logs.student_id')
                ->on('attendance_logs.scanned_at', '=', 'latest_logs.last_scanned_at');
        })
            ->where('attendance_logs.school_id', $schoolId)
            ->where('attendance_logs.type', 'in')
            ->count();

        // 6. Staff Status (Real Teachers Attendance)
        $staffMembers = \App\Models\User::where(function($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                  ->orWhereHas('schools', function($sq) use ($schoolId) {
                      $sq->where('schools.id', $schoolId)->where('school_user.role', 'teacher');
                  });
            })
            ->where('role', 'teacher')
            ->get();

        $totalStaff = $staffMembers->count();
        
        // Fetch latest logs for these teachers today
        $latestTeacherLogs = \App\Models\TeacherAttendanceLog::where('school_id', $schoolId)
            ->where('scanned_at', '>=', $today)
            ->where('scanned_at', '<', $tomorrow)
            ->orderBy('scanned_at', 'desc')
            ->get()
            ->groupBy('user_id');

        $staffData = $staffMembers->map(function($teacher) use ($latestTeacherLogs) {
            $userLogs = $latestTeacherLogs->get($teacher->id);
            $lastLog = $userLogs ? $userLogs->first() : null;
            $entryLog = $userLogs ? $userLogs->where('type', 'in')->last() : null; // First 'in' of the day

            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'avatar_url' => $teacher->avatar_url ?: ($teacher->profile_photo_path ? asset('storage/' . $teacher->profile_photo_path) : null),
                'status' => ($lastLog && $lastLog->type === 'in') ? 'present' : 'absent',
                'time' => $entryLog ? $entryLog->scanned_at->setTimezone(config('app.timezone', 'America/Mexico_City'))->format('h:i A') : '---',
            ];
        });

        $staffPresent = $staffData->where('status', 'present')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'totalStudents' => $totalStudents,
                'attendanceToday' => $attendanceToday,
                'attendanceRate' => $attendanceRate,
                'attendanceTrend' => $attendanceRate >= $prevRate ? '+' . ($attendanceRate - $prevRate) : '-' . ($prevRate - $attendanceRate),
                'absentCount' => $absent,
                'lateCount' => $late,
                'unclosedCount' => $unclosedCount,
                'staffPresent' => $staffPresent,
                'totalStaff' => $totalStaff,
                'entrySummary' => [
                    'onTime' => $onTime,
                    'late' => $late,
                    'absent' => $absent
                ],
                'groupStats' => $groupStats,
                'staff' => $staffData->take(8) // Limiting for dashboard view
            ]
        ]);
    }

    /**
     * Full Staff Status list for the director view.
     */
    public function getStaffStatus(Request $request)
    {
        $schoolId = $this->getSchoolId($request);

        if (!$schoolId) {
            return response()->json(['success' => false, 'message' => 'No se ha seleccionado una escuela.'], 403);
        }

        $school = \App\Models\School::find($schoolId);
        $timezone = $school && $school->timezone ? $school->timezone : 'America/Mexico_City';

        $localStart = $request->query('local_start');
        if ($localStart) {
            $localTodayStart = \Illuminate\Support\Carbon::parse(substr($localStart, 0, 10), $timezone)->startOfDay();
        } else {
            $localTodayStart = now($timezone)->startOfDay();
        }

        $today = $localTodayStart->copy()->setTimezone('UTC');
        $tomorrow = $localTodayStart->copy()->addDay()->setTimezone('UTC');

        $staffMembers = \App\Models\User::where(function($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                  ->orWhereHas('schools', function($sq) use ($schoolId) {
                      $sq->where('schools.id', $schoolId)->where('school_user.role', 'teacher');
                  });
            })
            ->where('role', 'teacher')
            ->orderBy('name')
            ->get();

        $latestTeacherLogs = \App\Models\TeacherAttendanceLog::where('school_id', $schoolId)
            ->where('scanned_at', '>=', $today)
            ->where('scanned_at', '<', $tomorrow)
            ->orderBy('scanned_at', 'desc')
            ->get()
            ->groupBy('user_id');

        $staffData = $staffMembers->map(function($teacher) use ($latestTeacherLogs, $timezone) {
            $userLogs = $latestTeacherLogs->get($teacher->id);
            $lastLog = $userLogs ? $userLogs->first() : null;
            $entryLog = $userLogs ? $userLogs->where('type', 'in')->last() : null;

            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'avatar_url' => $teacher->avatar_url ?: ($teacher->profile_photo_path ? asset('storage/' . $teacher->profile_photo_path) : null),
                'status' => ($lastLog && $lastLog->type === 'in') ? 'present' : 'absent',
                'time' => $entryLog ? $entryLog->scanned_at->setTimezone($timezone)->format('h:i A') : '---',
            ];
        });

        $present = $staffData->where('status', 'present')->count();
        $absent = $staffData->where('status', 'absent')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'staff' => $staffData->values(),
                'present' => $present,
                'absent' => $absent,
                'total' => $staffMembers->count()
            ]
        ]);
    }

    public function getUnclosedAttendance(Request $request)
    {
        $school_id = $this->getSchoolId($request) ?: $request->user()->school_id;
        $school = \App\Models\School::find($school_id);
        $timezone = $school && $school->timezone ? $school->timezone : 'America/Mexico_City';

        $localStart = $request->query('local_start');

        // Si no mandan local_start, intentamos deducirlo solo por fecha para el subquery
        if ($localStart) {
            $todayDateTime = \Illuminate\Support\Carbon::parse(substr($localStart, 0, 10), $timezone)->startOfDay()->setTimezone('UTC')->toDateTimeString();
            $tomorrowDateTime = \Illuminate\Support\Carbon::parse(substr($localStart, 0, 10), $timezone)->endOfDay()->setTimezone('UTC')->toDateTimeString();
        } else {
            $todayDateTime = now($timezone)->startOfDay()->setTimezone('UTC');
            $tomorrowDateTime = now($timezone)->endOfDay()->setTimezone('UTC');
        }

        $latestLogsSubquery = \App\Models\AttendanceLog::select('student_id', DB::raw('MAX(scanned_at) as last_scanned_at'))
            ->where('school_id', $school_id)
            ->whereBetween('scanned_at', [$todayDateTime, $tomorrowDateTime])
            ->groupBy('student_id');

        $unclosedRecords = \App\Models\AttendanceLog::joinSub($latestLogsSubquery, 'latest_logs', function ($join) {
            $join->on('attendance_logs.student_id', '=', 'latest_logs.student_id')
                ->on('attendance_logs.scanned_at', '=', 'latest_logs.last_scanned_at');
        })
            ->with(['student', 'student.classroom'])
            ->where('attendance_logs.type', 'in')
            ->where('attendance_logs.school_id', $school_id)
            ->get();

        // Add photo_url to each student
        $unclosedRecords->each(function ($record) {
            if ($record->student && $record->student->photo_path) {
                $record->student->photo_url = asset('storage/' . $record->student->photo_path);
            }
        });

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
        $user = $request->user();
        $query = \App\Models\Student::with('classroom');

        if ($user && $user->role === 'super_admin') {
            if ($request->has('school_id') && $request->school_id != '') {
                $query->where('school_id', $request->school_id);
            }
        } else {
            $query->where('school_id', $this->getSchoolId($request));
        }

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
        $user = $request->user();
        $schoolId = ($user && $user->role === 'super_admin' && $request->has('school_id')) 
            ? $request->school_id 
            : $this->getSchoolId($request);

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'enrollment_code' => 'required|string|unique:students,enrollment_code,NULL,id,school_id,' . $schoolId,
            'school_level' => 'required|string',
            'grade' => 'required|string',
            'group_letter' => 'required|string',
            'shift' => 'nullable|string',
        ]);

        $classroom = \App\Models\Classroom::firstOrCreate([
            'school_id' => $schoolId,
            'school_level' => $request->school_level,
            'grade' => $request->grade,
            'group_letter' => $request->group_letter,
            'shift' => $request->shift ?? 'matutino',
        ]);

        $data = $request->except(['photo_url', 'photo', 'school_level', 'grade', 'group_letter', 'shift', 'classroom', 'school', 'id', 'created_at', 'updated_at', 'deleted_at']);
        $data['school_id'] = $schoolId;
        $data['classroom_id'] = $classroom->id;

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
        $user = $request->user();
        $query = \App\Models\Student::with('classroom');
        if (!($user && $user->role === 'super_admin')) {
            $query->where('school_id', $this->getSchoolId($request));
        }
        $student = $query->findOrFail($id);
        $student->photo_url = $student->photo_path ? asset('storage/' . $student->photo_path) : null;

        return response()->json([
            'success' => true,
            'data' => $student
        ]);
    }

    public function updateStudent(Request $request, $id)
    {
        $user = $request->user();
        $query = \App\Models\Student::query();
        if (!($user && $user->role === 'super_admin')) {
            $query->where('school_id', $this->getSchoolId($request));
        }
        $student = $query->findOrFail($id);

        $schoolId = ($user && $user->role === 'super_admin' && $request->has('school_id')) 
            ? $request->school_id 
            : $student->school_id;

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'enrollment_code' => 'required|string|unique:students,enrollment_code,' . $id . ',id,school_id,' . $schoolId,
            'school_level' => 'required|string',
            'grade' => 'required|string',
            'group_letter' => 'required|string',
            'shift' => 'nullable|string',
        ]);

        $classroom = \App\Models\Classroom::firstOrCreate([
            'school_id' => $schoolId,
            'school_level' => $request->school_level,
            'grade' => $request->grade,
            'group_letter' => $request->group_letter,
            'shift' => $request->shift ?? 'matutino',
        ]);

        $data = $request->except(['photo_url', 'photo', 'school_level', 'grade', 'group_letter', 'shift', 'classroom', 'school', 'id', 'created_at', 'updated_at', 'deleted_at']);
        $data['classroom_id'] = $classroom->id;
        $data['school_id'] = $schoolId;

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
        $user = $request->user();
        $query = \App\Models\Student::query();
        if (!($user && $user->role === 'super_admin')) {
            $query->where('school_id', $this->getSchoolId($request));
        }
        $student = $query->findOrFail($id);

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
            'zip_file' => 'required|file|max:256000', // 250MB
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
        // Si school_id es null (super_admin sin escuela seleccionada), buscar en todas las escuelas
        $students = $school_id
            ? \App\Models\Student::where('school_id', $school_id)->get()
            : \App\Models\Student::all();

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
        // Normalizar Unicode a forma compuesta (NFC) para manejar archivos de macOS (NFD)
        if (function_exists('normalizer_normalize')) {
            $str = \Normalizer::normalize($str, \Normalizer::FORM_C);
        }
        $str = strtolower($str);
        $str = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
            $str
        );
        // Eliminar marcas diacriticas restantes (tildes, acentos combinantes)
        $str = preg_replace('/\pM/u', '', $str);
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
    /**
     * Reports: Attendance statistics for director reports view.
     * Supports date range: week, month, quarter.
     */
    public function getReportsData(Request $request)
    {
        $schoolId = $this->getSchoolId($request);

        if (!$schoolId) {
            return response()->json(['success' => false, 'message' => 'No se ha seleccionado una escuela.'], 403);
        }

        $school = School::find($schoolId);
        $timezone = $school && $school->timezone ? $school->timezone : 'America/Mexico_City';

        // Calculate date range
        $range = $request->query('range', 'week');
        $now = now($timezone);
        $endDate = $now->copy()->endOfDay()->setTimezone('UTC');

        switch ($range) {
            case 'day':
                $startDate = $now->copy()->startOfDay()->setTimezone('UTC');
                break;
            case 'custom':
                $customDate = $request->query('date');
                if ($customDate) {
                    $parsedDate = \Illuminate\Support\Carbon::parse($customDate, $timezone);
                    $startDate = $parsedDate->copy()->startOfDay()->setTimezone('UTC');
                    $endDate = $parsedDate->copy()->endOfDay()->setTimezone('UTC');
                } else {
                    $startDate = $now->copy()->startOfDay()->setTimezone('UTC');
                }
                break;
            case 'month':
                $startDate = $now->copy()->startOfMonth()->startOfDay()->setTimezone('UTC');
                break;
            case 'quarter':
                $startDate = $now->copy()->subMonths(3)->startOfDay()->setTimezone('UTC');
                break;
            case 'week':
            default:
                $startDate = $now->copy()->startOfWeek()->startOfDay()->setTimezone('UTC');
                break;
        }

        // Count school days as unique dates with actual attendance records in range
        // This avoids counting periods before the school started using the system
        $schoolDays = \App\Models\AttendanceLog::where('school_id', $schoolId)
            ->where('type', 'in')
            ->where('scanned_at', '>=', $startDate)
            ->where('scanned_at', '<=', $endDate)
            ->selectRaw("COUNT(DISTINCT DATE(CONVERT_TZ(scanned_at, '+00:00', ?))) as days_count", [$this->tzOffset($timezone)])
            ->value('days_count');
        $schoolDays = max($schoolDays, 1);

        // Get all students for this school with their classroom
        $students = Student::with('classroom')
            ->where('school_id', $schoolId)
            ->where('is_active', 1)
            ->orderBy('last_name')
            ->get();

        $totalStudents = $students->count();

        // Get all entry attendance logs in the range, grouped by student and date
        $logs = \App\Models\AttendanceLog::where('school_id', $schoolId)
            ->where('type', 'in')
            ->where('scanned_at', '>=', $startDate)
            ->where('scanned_at', '<=', $endDate)
            ->select('student_id', 'status', DB::raw("DATE(CONVERT_TZ(scanned_at, '+00:00', '" . $this->tzOffset($timezone) . "')) as att_date"))
            ->groupBy('student_id', 'att_date', 'status')
            ->get();

        // Build per-student stats
        $studentLogsMap = [];
        foreach ($logs as $log) {
            $sid = $log->student_id;
            if (!isset($studentLogsMap[$sid])) {
                $studentLogsMap[$sid] = ['present' => 0, 'late' => 0, 'absent' => 0, 'days' => []];
            }
            if (!in_array($log->att_date, $studentLogsMap[$sid]['days'])) {
                $studentLogsMap[$sid]['days'][] = $log->att_date;
            }
            if ($log->status === 'late') {
                $studentLogsMap[$sid]['late']++;
            } else {
                $studentLogsMap[$sid]['present']++;
            }
        }

        // Compute per-student statistics
        $totalPresent = 0;
        $totalAbsences = 0;
        $totalLates = 0;
        $studentStatsArr = [];
        $gradeAgg = []; // grade => [totalStudents, present, absences, lates, groups => set]
        $groupAgg = []; // "gradeºletter" => [totalStudents, present, absences, lates]

        foreach ($students as $student) {
            $grade = $student->classroom ? $student->classroom->grade : '?';
            $groupLetter = $student->classroom ? $student->classroom->group_letter : '?';
            $groupKey = $grade . 'º' . $groupLetter;

            $sLog = $studentLogsMap[$student->id] ?? null;
            $daysAttended = $sLog ? count($sLog['days']) : 0;
            $lates = $sLog ? $sLog['late'] : 0;
            $absences = $schoolDays - $daysAttended;
            if ($absences < 0) $absences = 0;
            $attendance = $schoolDays > 0 ? round(($daysAttended / $schoolDays) * 100, 1) : 0;

            $status = 'regular';
            if ($attendance < 75) $status = 'critical';
            elseif ($attendance < 90) $status = 'warning';

            $totalPresent += $daysAttended;
            $totalAbsences += $absences;
            $totalLates += $lates;

            $studentStatsArr[] = [
                'id' => $student->id,
                'name' => $student->first_name,
                'lastName' => $student->last_name,
                'enrollmentCode' => $student->enrollment_code,
                'grade' => $grade,
                'group' => $groupLetter,
                'attendance' => $attendance,
                'daysAttended' => $daysAttended,
                'totalDays' => $schoolDays,
                'absences' => $absences,
                'lates' => $lates,
                'status' => $status
            ];

            // Grade aggregation
            if (!isset($gradeAgg[$grade])) {
                $gradeAgg[$grade] = ['totalStudents' => 0, 'present' => 0, 'absences' => 0, 'lates' => 0, 'groupSet' => []];
            }
            $gradeAgg[$grade]['totalStudents']++;
            $gradeAgg[$grade]['present'] += $daysAttended;
            $gradeAgg[$grade]['absences'] += $absences;
            $gradeAgg[$grade]['lates'] += $lates;
            if (!in_array($groupLetter, $gradeAgg[$grade]['groupSet'])) {
                $gradeAgg[$grade]['groupSet'][] = $groupLetter;
            }

            // Group aggregation
            if (!isset($groupAgg[$groupKey])) {
                $groupAgg[$groupKey] = ['grade' => $grade, 'totalStudents' => 0, 'present' => 0, 'absences' => 0, 'lates' => 0];
            }
            $groupAgg[$groupKey]['totalStudents']++;
            $groupAgg[$groupKey]['present'] += $daysAttended;
            $groupAgg[$groupKey]['absences'] += $absences;
            $groupAgg[$groupKey]['lates'] += $lates;
        }

        // Format grade stats
        $gradeStats = [];
        ksort($gradeAgg);
        foreach ($gradeAgg as $grade => $data) {
            $maxPossible = $data['totalStudents'] * $schoolDays;
            $gradeStats[] = [
                'grade' => $grade,
                'totalStudents' => $data['totalStudents'],
                'groups' => count($data['groupSet']),
                'attendance' => $maxPossible > 0 ? round(($data['present'] / $maxPossible) * 100, 1) : 0,
                'present' => $data['present'],
                'absences' => $data['absences'],
                'lates' => $data['lates']
            ];
        }

        // Format group stats
        $groupStats = [];
        ksort($groupAgg);
        foreach ($groupAgg as $name => $data) {
            $maxPossible = $data['totalStudents'] * $schoolDays;
            $groupStats[] = [
                'name' => $name,
                'grade' => $data['grade'],
                'totalStudents' => $data['totalStudents'],
                'attendance' => $maxPossible > 0 ? round(($data['present'] / $maxPossible) * 100, 1) : 0,
                'present' => $data['present'],
                'absences' => $data['absences'],
                'lates' => $data['lates']
            ];
        }

        // Average attendance
        $maxTotal = $totalStudents * $schoolDays;
        $avgAttendance = $maxTotal > 0 ? round(($totalPresent / $maxTotal) * 100, 1) : 0;

        // Get the earliest attendance record date for the school (for calendar min date)
        $earliestLog = \App\Models\AttendanceLog::where('school_id', $schoolId)
            ->where('type', 'in')
            ->orderBy('scanned_at', 'asc')
            ->first();
        $minDate = $earliestLog
            ? $earliestLog->scanned_at->copy()->setTimezone($timezone)->format('Y-m-d')
            : now($timezone)->format('Y-m-d');

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'totalStudents' => $totalStudents,
                    'avgAttendance' => $avgAttendance,
                    'totalAbsences' => $totalAbsences,
                    'totalLates' => $totalLates,
                    'schoolDays' => $schoolDays
                ],
                'minDate' => $minDate,
                'gradeStats' => $gradeStats,
                'groupStats' => $groupStats,
                'studentStats' => $studentStatsArr
            ]
        ]);
    }

    /**
     * Helper: get UTC offset string for a timezone (for CONVERT_TZ).
     */
    private function tzOffset(string $tz): string
    {
        $dt = new \DateTime('now', new \DateTimeZone($tz));
        $offset = $dt->getOffset();
        $sign = $offset >= 0 ? '+' : '-';
        $hours = str_pad(abs(intdiv($offset, 3600)), 2, '0', STR_PAD_LEFT);
        $mins = str_pad(abs(($offset % 3600) / 60), 2, '0', STR_PAD_LEFT);
        return $sign . $hours . ':' . $mins;
    }

}
