<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use App\Traits\HandlesSchoolContext;

class TeacherDashboardController extends Controller
{
    use HandlesSchoolContext;

    public function getDashboardInfo(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $schoolId = $this->getSchoolId($request);

        // Relación: TeacherGroupAssignment y luego ->classroom (Como se ve en TeacherController)
        $teacherGroups = $user->teacherGroups()->with('classroom')->get();
        $assignedClasses = $teacherGroups->map(function ($tg) {
            $c = $tg->classroom;
            return [
                'id' => $c->id,
                'name' => $c->grade . 'º ' . $c->group_letter . ' - ' . ($c->name ?? 'General')
            ];
        })->values()->toArray();

        if (empty($assignedClasses)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'currentClass' => [
                        'id' => null,
                        'name' => 'Sin clase asignada'
                    ],
                    'assignedClasses' => [],
                    'stats' => ['total' => 0, 'present' => 0, 'late' => 0, 'absent' => 0],
                    'students' => [],
                    'recentActivity' => []
                ]
            ]);
        }

        $classroomTargetId = $request->query('classroom_id');
        $classroom = null;

        if ($classroomTargetId) {
            $selectedGroup = $teacherGroups->firstWhere('classroom_id', $classroomTargetId);
            if ($selectedGroup) {
                $classroom = $selectedGroup->classroom;
            }
        }

        if (!$classroom) {
            $classroom = $teacherGroups->first()->classroom;
        }

        $students = Student::where('classroom_id', $classroom->id)
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->get();

        $totalStudents = $students->count();

        $school = \App\Models\School::find($schoolId);
        $timezone = $school && $school->timezone ? $school->timezone : 'America/Mexico_City';

        $localTodayStart = Carbon::now($timezone)->startOfDay();
        $localTodayEnd = Carbon::now($timezone)->endOfDay();

        if ($request->has('local_start')) {
            try {
                $localTodayStart = Carbon::parse(substr($request->local_start, 0, 10), $timezone)->startOfDay();
                $localTodayEnd = $localTodayStart->copy()->endOfDay();
            } catch (\Exception $e) {
            }
        }

        $utcQueryStart = $localTodayStart->copy()->setTimezone('UTC')->toDateTimeString();
        $utcQueryEnd = $localTodayEnd->copy()->setTimezone('UTC')->toDateTimeString();

        $studentIds = $students->pluck('id')->toArray();

        $logs = AttendanceLog::whereIn('student_id', $studentIds)
            ->where('school_id', $schoolId)
            ->whereBetween('scanned_at', [$utcQueryStart, $utcQueryEnd])
            ->orderBy('scanned_at', 'asc')
            ->get()
            ->groupBy('student_id');

        $present = 0;
        $late = 0;
        $absent = 0;

        // Asumiendo que las 8:01 AM es retardo
        $lateTimeThreshold = $localTodayStart->copy()->setHour(8)->setMinute(1);

        $studentsData = [];
        $recentActivity = [];

        foreach ($students as $student) {
            $status = 'absent';
            $timeLabel = null;
            $avatar = $student->photo_url ?: null;

            if ($logs->has($student->id)) {
                $studentLogs = $logs->get($student->id);
                $firstLog = $studentLogs->first(); // Primer scan del día (entrada)

                $scanTime = Carbon::parse($firstLog->scanned_at, 'UTC')->setTimezone($timezone);

                if ($scanTime->greaterThanOrEqualTo($lateTimeThreshold)) {
                    $status = 'late';
                    $late++;
                } else {
                    $status = 'present';
                    $present++;
                }
                $timeLabel = $scanTime->format('h:i A');

                // Llenar actividad reciente (Mock simple con datos reales)
                $recentActivity[] = [
                    'id' => $firstLog->id,
                    'type' => $status,
                    'subject' => $student->first_name,
                    'action' => $status === 'late' ? 'llegó con retardo' : 'marcó su asistencia',
                    'time' => $timeLabel,
                    'quote' => null
                ];
            } else {
                $absent++;
            }

            $studentsData[] = [
                'id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
                'id_number' => '#' . $student->enrollment_code,
                'status' => $status,
                'time' => $timeLabel,
                'avatar' => $avatar
            ];
        }

        // Ordenar actividad de más reciente a más antigua y limitar a 4
        usort($recentActivity, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
        $recentActivity = array_slice($recentActivity, 0, 4);

        if (empty($recentActivity)) {
            $recentActivity[] = [
                'id' => 999,
                'type' => 'system',
                'subject' => 'Sistema',
                'action' => 'Dashboard sincronizado',
                'time' => Carbon::now($timezone)->format('h:i A'),
                'quote' => null
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'currentClass' => [
                    'id' => $classroom->id,
                    'name' => $classroom->grade . 'º ' . $classroom->group_letter . ' - ' . ($classroom->name ?? 'General')
                ],
                'assignedClasses' => $assignedClasses,
                'stats' => [
                    'total' => $totalStudents,
                    'present' => $present,
                    'late' => $late,
                    'absent' => $absent
                ],
                'students' => $studentsData,
                'recentActivity' => $recentActivity
            ]
        ]);
    }
}
