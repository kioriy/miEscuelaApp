<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TeacherGroupAssignment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Traits\HandlesSchoolContext;

class TeacherController extends Controller
{
    use HandlesSchoolContext;

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId($request);
        $search = $request->input('search');
        $grade = $request->input('grade');

        $teachers = User::where(function($q) use ($schoolId) {
            // Include users who are natively 'teacher' for this school
            $q->where(function($subq) use ($schoolId) {
                $subq->where('school_id', $schoolId)
                     ->where('role', 'teacher');
            })
            // Or users who have the 'teacher' role in the pivot table for this school
            ->orWhereHas('schools', function($sq) use ($schoolId) {
                $sq->where('schools.id', $schoolId)
                   ->where('school_user.role', 'teacher');
            });
        })
        ->when($search, function($q) use ($search) {
            $q->where(function($subq) use ($search) {
                $subq->where('name', 'LIKE', "%{$search}%")
                     ->orWhere('email', 'LIKE', "%{$search}%");
            });
        })
        ->when($grade, function($q) use ($grade) {
            $q->whereHas('teacherGroups.classroom', function($sq) use ($grade) {
                $sq->where('grade', $grade);
            });
        })
        ->with('teacherGroups.classroom')
        ->get();

        // Map data for the frontend
        $mapped = $teachers->map(function ($teacher) {
            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'enrollment_code' => $teacher->enrollment_code,
                'photo' => $teacher->avatar_url ?: ($teacher->profile_photo_path ? asset('storage/' . $teacher->profile_photo_path) : null),
                // Combine grade and group_letter for the badges e.g. "1ro A"
                'grades' => $teacher->teacherGroups->filter(fn($g) => $g->classroom)->map(function ($group) {
                    return $group->classroom->grade . 'º ' . $group->classroom->group_letter;
                })->toArray(),
                // For now, hardcode Presente/Ausente, later we can check today's AttendanceLog
                'status' => 'Ausente'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $mapped
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'enrollment_code' => 'nullable|string|unique:users,enrollment_code',
            'groups' => 'array' // Array of classroom IDs
        ]);

        $schoolId = $this->getSchoolId($request);

        $enrollmentCode = $request->enrollment_code;
        if (!$enrollmentCode) {
            // Generar código automático: PROF-YY-RANDOM
            $year = date('y');
            do {
                $enrollmentCode = 'PROF-' . $year . '-' . strtoupper(Str::random(4));
            } while (User::where('enrollment_code', $enrollmentCode)->exists());
        }

        $teacher = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(Str::random(12)),
            'role' => 'teacher',
            'school_id' => $schoolId,
            'enrollment_code' => $enrollmentCode,
        ]);

        if ($schoolId) {
            $teacher->schools()->attach($schoolId, ['role' => 'teacher']);
        }

        if ($request->has('groups')) {
            foreach ($request->groups as $classroomId) {
                $classroom = \App\Models\Classroom::find($classroomId);
                if ($classroom) {
                    TeacherGroupAssignment::create([
                        'user_id' => $teacher->id,
                        'classroom_id' => $classroomId,
                        'school_id' => $classroom->school_id,
                        'school_level' => $classroom->school_level,
                        'shift' => $classroom->shift,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Profesor registrado correctamente.',
            'data' => $teacher
        ]);
    }

    public function show(Request $request, $id)
    {
        $schoolId = $this->getSchoolId($request);
        $teacher = User::where(function ($q) use ($schoolId) {
            $q->whereHas('schools', function ($sq) use ($schoolId) {
                $sq->where('schools.id', $schoolId);
            })->orWhere('school_id', $schoolId);
        })
            ->where(function($q) {
                $q->where('role', 'teacher')
                  ->orWhereHas('schools', function($sq) {
                      $sq->where('school_user.role', 'teacher');
                  });
            })
            ->with('teacherGroups.classroom')
            ->findOrFail($id);

        // Get today's attendance
        $today = now()->startOfDay();
        $todayLogs = \App\Models\TeacherAttendanceLog::where('user_id', $teacher->id)
            ->where('school_id', $schoolId)
            ->whereDate('scanned_at', $today)
            ->orderBy('scanned_at')
            ->get();

        $entryLog = $todayLogs->where('type', 'in')->first();
        $exitLog = $todayLogs->where('type', 'out')->last();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'enrollment_code' => $teacher->enrollment_code,
                'photo' => $teacher->avatar_url ?: ($teacher->profile_photo_path ? asset('storage/' . $teacher->profile_photo_path) : null),
                'groups' => $teacher->teacherGroups->pluck('classroom_id')->toArray(),
                'classrooms' => $teacher->teacherGroups->filter(fn($g) => $g->classroom)->map(function ($g) {
                    return [
                        'id' => $g->classroom->id,
                        'grade' => $g->classroom->grade,
                        'group_letter' => $g->classroom->group_letter,
                        'school_level' => $g->classroom->school_level,
                        'shift' => $g->classroom->shift,
                    ];
                })->values(),
                'today_attendance' => [
                    'status' => $entryLog ? ($entryLog->status ?? 'present') : 'absent',
                    'entry_time' => $entryLog ? $entryLog->scanned_at->format('h:i A') : null,
                    'exit_time' => $exitLog ? $exitLog->scanned_at->format('h:i A') : null,
                ],
                'created_at' => $teacher->created_at?->format('d/m/Y'),
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $schoolId = $this->getSchoolId($request);
        $teacher = User::where(function ($q) use ($schoolId) {
            $q->whereHas('schools', function ($sq) use ($schoolId) {
                $sq->where('schools.id', $schoolId);
            })->orWhere('school_id', $schoolId);
        })
            ->where('role', 'teacher')
            ->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $teacher->id,
            'enrollment_code' => 'nullable|string|unique:users,enrollment_code,' . $teacher->id,
            'groups' => 'array'
        ]);

        $teacher->update([
            'name' => $request->name,
            'email' => $request->email,
            'enrollment_code' => $request->enrollment_code ?? $teacher->enrollment_code,
        ]);

        if ($request->has('groups')) {
            // Re-sync groups by deleting old ones and creating new ones
            $teacher->teacherGroups()->delete();
            foreach ($request->groups as $classroomId) {
                $classroom = \App\Models\Classroom::find($classroomId);
                if ($classroom) {
                    TeacherGroupAssignment::create([
                        'user_id' => $teacher->id,
                        'classroom_id' => $classroomId,
                        'school_id' => $classroom->school_id,
                        'school_level' => $classroom->school_level,
                        'shift' => $classroom->shift,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Profesor actualizado correctamente.',
            'data' => $teacher
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $schoolId = $this->getSchoolId($request);
        $teacher = User::where(function ($q) use ($schoolId) {
            $q->whereHas('schools', function ($sq) use ($schoolId) {
                $sq->where('schools.id', $schoolId);
            })->orWhere('school_id', $schoolId);
        })
            ->where('role', 'teacher')
            ->findOrFail($id);

        $teacher->delete(); // Cascades to teacher_group_assignments because of foreign key

        return response()->json([
            'success' => true,
            'message' => 'Profesor eliminado correctamente.'
        ]);
    }
}
