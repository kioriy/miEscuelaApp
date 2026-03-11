<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\AnnouncementTarget;
use App\Models\Student;
use App\Models\Classroom;
use App\Traits\HandlesSchoolContext;
use Illuminate\Support\Facades\DB;

class MessagingController extends Controller
{
    use HandlesSchoolContext;

    /**
     * Get the classrooms and students assigned to the teacher.
     */
    public function getTeacherContext(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $schoolId = $this->getSchoolId($request);

        // Get classrooms assigned to the teacher
        $teacherGroups = $user->teacherGroups()->with('classroom')->get();
        $classrooms = $teacherGroups->map(function ($tg) {
            return [
                'id' => $tg->classroom->id,
                'name' => "{$tg->classroom->grade}º {$tg->classroom->group_letter} - " . ($tg->classroom->name ?? 'General'),
                'level' => $tg->classroom->school_level,
                'grade' => $tg->classroom->grade,
                'group_letter' => $tg->classroom->group_letter,
                'shift' => $tg->classroom->shift
            ];
        });

        // Get all students for these classrooms
        $classroomIds = $teacherGroups->pluck('classroom_id')->toArray();
        $students = Student::whereIn('classroom_id', $classroomIds)
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'full_name' => "{$s->first_name} {$s->last_name}",
                    'classroom_id' => $s->classroom_id
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'classrooms' => $classrooms,
                'students' => $students
            ]
        ]);
    }

    /**
     * Send a message to a group or specific student.
     */
    public function sendMessage(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'type' => 'required|in:group,student',
            'target_id' => 'required|integer',
            'title' => 'required|string|max:150',
            'content' => 'required|string',
        ]);

        $schoolId = $this->getSchoolId($request);

        try {
            DB::beginTransaction();

            // 1. Create the main announcement
            $announcement = Announcement::create([
                'school_id' => $schoolId,
                'created_by_user_id' => $user->id,
                'title' => $request->title,
                'content' => $request->content,
                'is_general' => false
            ]);

            // 2. Create the target entry
            $targetData = [
                'announcement_id' => $announcement->id,
            ];

            if ($request->type === 'group') {
                $classroom = Classroom::findOrFail($request->target_id);
                // Verify teacher has access to this classroom
                if (!$user->teacherGroups()->where('classroom_id', $classroom->id)->exists()) {
                    throw new \Exception('No tiene permisos para este grupo.');
                }

                $targetData = array_merge($targetData, [
                    'school_level' => $classroom->school_level,
                    'grade' => $classroom->grade,
                    'group_letter' => $classroom->group_letter,
                    'shift' => $classroom->shift,
                    'student_id' => null
                ]);
            } else {
                $student = Student::findOrFail($request->target_id);
                // Verify teacher has access to this student's classroom
                if (!$user->teacherGroups()->where('classroom_id', $student->classroom_id)->exists()) {
                    throw new \Exception('No tiene permisos para este alumno.');
                }

                $targetData = array_merge($targetData, [
                    'student_id' => $student->id,
                    'school_level' => null,
                    'grade' => null,
                    'group_letter' => null,
                    'shift' => null
                ]);
            }

            AnnouncementTarget::create($targetData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el mensaje: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the history of messages sent by the teacher.
     */
    public function getSentMessages(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $announcements = Announcement::where('created_by_user_id', $user->id)
            ->with(['targets.student'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($a) {
                // Determine recipient label
                $recipients = $a->targets->map(function ($t) {
                    if ($t->student_id) {
                        return "{$t->student->first_name} {$t->student->last_name}";
                    }
                    return "{$t->grade}º {$t->group_letter} ({$t->school_level})";
                })->implode(', ');

                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'content' => $a->content,
                    'recipients' => $recipients,
                    'created_at' => $a->created_at->format('d/m/Y H:i'),
                    'time_ago' => $a->created_at->diffForHumans()
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $announcements
        ]);
    }
}
