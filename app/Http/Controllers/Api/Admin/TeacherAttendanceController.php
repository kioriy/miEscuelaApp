<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use App\Traits\HandlesSchoolContext;

class TeacherAttendanceController extends Controller
{
    use HandlesSchoolContext;

    /**
     * Get pending students for a specific classroom today
     */
    public function getPending(Request $request, $classroomId)
    {
        $user = $request->user();
        if ($user->role !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $schoolId = $this->getSchoolId($request);
        $school = \App\Models\School::find($schoolId);
        $timezone = $school && $school->timezone ? $school->timezone : 'America/Mexico_City';

        // Verify teacher has access to this classroom
        $hasAccess = $user->teacherGroups()->where('classroom_id', $classroomId)->exists();
        if (!$hasAccess) {
            return response()->json(['success' => false, 'message' => 'Unauthorized for this class'], 403);
        }

        $localTodayStart = Carbon::now($timezone)->startOfDay();
        $localTodayEnd = Carbon::now($timezone)->endOfDay();

        $utcQueryStart = $localTodayStart->copy()->setTimezone('UTC');
        $utcQueryEnd = $localTodayEnd->copy()->setTimezone('UTC');

        // Get all active students for this classroom
        $students = Student::where('classroom_id', $classroomId)
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->get();

        $studentIds = $students->pluck('id')->toArray();

        // Get today's attendance logs for these students
        $logs = AttendanceLog::whereIn('student_id', $studentIds)
            ->where('school_id', $schoolId)
            ->whereBetween('scanned_at', [$utcQueryStart, $utcQueryEnd])
            ->get()
            ->groupBy('student_id');

        // Filter out students who already have an attendance record for today
        $pendingStudents = [];
        $presentCount = 0;
        $absentCount = 0;

        foreach ($students as $student) {
            if ($logs->has($student->id)) {
                $studentLogs = $logs->get($student->id);
                // We count any record today as "handled" (present, excused, late, etc.)
                // Except if it's explicitly 'absent', then they might be considered handled too.
                $hasHandledRecord = false;
                $isAbsent = false;
                foreach ($studentLogs as $log) {
                    if ($log->status === 'absent') {
                        $isAbsent = true;
                        $hasHandledRecord = true;
                    } else if (in_array($log->status, ['present', 'late', 'excused'])) {
                        $hasHandledRecord = true;
                    }
                }

                if ($hasHandledRecord) {
                    if ($isAbsent) {
                        $absentCount++;
                    } else {
                        $presentCount++;
                    }
                    continue; // Skip, not pending
                }
            }

            // Student is pending
            $pendingStudents[] = [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'enrollment_code' => $student->enrollment_code,
                'avatar' => $student->photo_url ?: null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'pending' => $pendingStudents,
                'stats' => [
                    'total' => $students->count(),
                    'present' => $presentCount,
                    'absent' => $absentCount + count($pendingStudents) // Currently pending are considered absent until marked otherwise
                ]
            ]
        ]);
    }

    /**
     * Mark a student's attendance
     */
    public function markAttendance(Request $request, $classroomId)
    {
        $user = $request->user();
        if ($user->role !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'status' => 'required|in:present,excused',
            'notes' => 'nullable|string|max:255'
        ]);

        $schoolId = $this->getSchoolId($request);

        // Ensure student belongs to this classroom and school
        $student = Student::where('id', $request->student_id)
            ->where('classroom_id', $classroomId)
            ->where('school_id', $schoolId)
            ->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found or unauthorized'], 404);
        }

        $school = \App\Models\School::find($schoolId);
        $timezone = $school && $school->timezone ? $school->timezone : 'America/Mexico_City';

        // Determine status (check for late if marking present)
        $status = $request->status;
        if ($status === 'present') {
            $localNow = Carbon::now($timezone);
            $localTodayStart = $localNow->copy()->startOfDay();

            $entryTime = $school->entry_time ?? '08:00:00';
            $tolerance = max($school->tolerance_minutes ?? 15, 30); // Use 30 minutes minimum for teacher roll call
            $lateTimeThreshold = $localTodayStart->copy()->setTimeFromTimeString($entryTime)->addMinutes($tolerance + 1);

            if ($localNow->greaterThanOrEqualTo($lateTimeThreshold)) {
                $status = 'late';
            }
        }

        $log = AttendanceLog::create([
            'student_id' => $student->id,
            'school_id' => $schoolId,
            'type' => 'in', // Marking from teacher panel is considered an entry
            'status' => $status,
            'notes' => $request->notes,
            'recorded_by_user_id' => $user->id,
            'scanned_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully',
            'data' => $log
        ]);
    }
}
