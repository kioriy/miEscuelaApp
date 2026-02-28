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

        $teachers = User::whereHas('schools', function ($q) use ($schoolId) {
            $q->where('schools.id', $schoolId);
        })->orWhere('school_id', $schoolId)
            ->where('role', 'teacher')
            ->with('teacherGroups')
            ->get();

        // Map data for the frontend
        $mapped = $teachers->map(function ($teacher) {
            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'photo' => $teacher->avatar_url ?: ($teacher->profile_photo_path ? asset('storage/' . $teacher->profile_photo_path) : null),
                'is_active' => $teacher->is_active,
                // Combine grade and group_letter for the badges e.g. "1ro A"
                'grades' => $teacher->teacherGroups->map(function ($group) {
                    return $group->grade . 'º ' . $group->group_letter;
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
            'is_active' => 'boolean',
            'groups' => 'array' // Array of "1-A", "2-B" strings
        ]);

        $schoolId = $this->getSchoolId($request);

        $teacher = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(Str::random(12)),
            'role' => 'teacher',
            'school_id' => $schoolId,
            'is_active' => $request->is_active ?? true,
        ]);

        if ($schoolId) {
            $teacher->schools()->attach($schoolId, ['role' => 'teacher']);
        }

        if ($request->has('groups')) {
            foreach ($request->groups as $groupStr) {
                // Parse "1-A"
                $parts = explode('-', $groupStr);
                if (count($parts) === 2) {
                    TeacherGroupAssignment::create([
                        'user_id' => $teacher->id,
                        'grade' => $parts[0],
                        'group_letter' => $parts[1]
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
            ->where('role', 'teacher')
            ->with('teacherGroups')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'is_active' => $teacher->is_active,
                'groups' => $teacher->teacherGroups->map(function ($group) {
                    return $group->grade . '-' . $group->group_letter;
                })->toArray()
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
            'is_active' => 'boolean',
            'groups' => 'array'
        ]);

        $teacher->update([
            'name' => $request->name,
            'email' => $request->email,
            'is_active' => $request->is_active ?? true,
        ]);

        if ($request->has('groups')) {
            // Re-sync groups by deleting old ones and creating new ones
            $teacher->teacherGroups()->delete();
            foreach ($request->groups as $groupStr) {
                $parts = explode('-', $groupStr);
                if (count($parts) === 2) {
                    TeacherGroupAssignment::create([
                        'user_id' => $teacher->id,
                        'grade' => $parts[0],
                        'group_letter' => $parts[1]
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
