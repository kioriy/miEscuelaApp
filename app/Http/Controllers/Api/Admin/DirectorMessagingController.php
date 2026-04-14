<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\AnnouncementTarget;
use App\Models\Student;
use App\Models\Classroom;
use App\Traits\HandlesSchoolContext;
use Illuminate\Support\Facades\DB;

class DirectorMessagingController extends Controller
{
    use HandlesSchoolContext;

    /**
     * Get the classrooms and students for the director's school.
     */
    public function getContext(Request $request)
    {
        $schoolId = $this->getSchoolId($request);
        if (!$schoolId) {
            return response()->json(['success' => false, 'message' => 'No school context found'], 403);
        }

        // Get all classrooms for the school
        $classrooms = Classroom::where('school_id', $schoolId)
            ->orderBy('grade')
            ->orderBy('group_letter')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => "{$c->grade}º {$c->group_letter} - " . ($c->name ?? 'General'),
                    'level' => $c->school_level,
                    'grade' => $c->grade,
                    'group_letter' => $c->group_letter,
                    'shift' => $c->shift
                ];
            });

        // Get all active students for the school
        $students = Student::where('school_id', $schoolId)
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
     * Send a message to the whole school, a group, or a specific student.
     */
    public function sendMessage(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'type' => 'required|in:all_school,group,student',
            'target_id' => 'required_if:type,group,student',
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
                'is_general' => $request->type === 'all_school'
            ]);

            // 2. Create the target entry if not for the whole school
            if ($request->type !== 'all_school') {
                $targetData = [
                    'announcement_id' => $announcement->id,
                ];

                if ($request->type === 'group') {
                    $classroom = Classroom::where('school_id', $schoolId)->findOrFail($request->target_id);
                    $targetData = array_merge($targetData, [
                        'school_level' => $classroom->school_level,
                        'grade' => $classroom->grade,
                        'group_letter' => $classroom->group_letter,
                        'shift' => $classroom->shift,
                        'student_id' => null
                    ]);
                } else {
                    $student = Student::where('school_id', $schoolId)->findOrFail($request->target_id);
                    $targetData = array_merge($targetData, [
                        'student_id' => $student->id,
                        'school_level' => null,
                        'grade' => null,
                        'group_letter' => null,
                        'shift' => null
                    ]);
                }

                AnnouncementTarget::create($targetData);
            }

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
     * Update an existing announcement (title and content only).
     * The director can only edit announcements they created for their school.
     */
    public function updateMessage(Request $request, $id)
    {
        $user = $request->user();
        $schoolId = $this->getSchoolId($request);

        if (!$schoolId) {
            return response()->json(['success' => false, 'message' => 'No school context found'], 403);
        }

        $request->validate([
            'title'   => 'required|string|max:150',
            'content' => 'required|string',
        ]);

        $announcement = Announcement::where('id', $id)
            ->where('school_id', $schoolId)
            ->where('created_by_user_id', $user->id)
            ->firstOrFail();

        $announcement->update([
            'title'   => $request->title,
            'content' => $request->content,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mensaje actualizado correctamente.',
        ]);
    }

    /**
     * Get the history of messages sent by the director.
     */
    public function getSentMessages(Request $request)
    {
        $user = $request->user();
        $announcements = Announcement::where('created_by_user_id', $user->id)
            ->with(['targets.student'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($a) {
                // Determine recipient label
                if ($a->is_general) {
                    $recipients = "Toda la Escuela";
                } else {
                    $recipients = $a->targets->map(function ($t) {
                        if ($t->student_id) {
                            return "{$t->student->first_name} {$t->student->last_name}";
                        }
                        return "{$t->grade}º {$t->group_letter} ({$t->school_level})";
                    })->implode(', ');
                }

                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'content' => $a->content,
                    'recipients' => $recipients,
                    'is_general' => $a->is_general,
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
