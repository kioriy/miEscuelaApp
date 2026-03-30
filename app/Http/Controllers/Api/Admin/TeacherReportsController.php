<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\School;
use Illuminate\Support\Facades\DB;
use App\Traits\HandlesSchoolContext;

class TeacherReportsController extends Controller
{
    use HandlesSchoolContext;

    /**
     * Reports: Attendance statistics scoped to teacher's assigned classrooms.
     * Supports date range: day, week, month, quarter.
     */
    public function getReportsData(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $schoolId = $this->getSchoolId($request);

        if (!$schoolId) {
            return response()->json(['success' => false, 'message' => 'No se ha seleccionado una escuela.'], 403);
        }

        // Get teacher's assigned classroom IDs
        $assignedClassroomIds = $user->teacherGroups()->pluck('classroom_id')->toArray();

        if (empty($assignedClassroomIds)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'totalStudents' => 0,
                        'avgAttendance' => 0,
                        'totalAbsences' => 0,
                        'totalLates' => 0,
                        'schoolDays' => 0
                    ],
                    'gradeStats' => [],
                    'groupStats' => [],
                    'studentStats' => []
                ]
            ]);
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

        // Get students only from the teacher's assigned classrooms
        $students = Student::with('classroom')
            ->where('school_id', $schoolId)
            ->whereIn('classroom_id', $assignedClassroomIds)
            ->where('is_active', 1)
            ->orderBy('last_name')
            ->get();

        $totalStudents = $students->count();
        $studentIds = $students->pluck('id')->toArray();

        // Count school days from actual attendance records for these students
        $schoolDays = \App\Models\AttendanceLog::where('school_id', $schoolId)
            ->where('type', 'in')
            ->whereIn('student_id', $studentIds)
            ->where('scanned_at', '>=', $startDate)
            ->where('scanned_at', '<=', $endDate)
            ->selectRaw("COUNT(DISTINCT DATE(CONVERT_TZ(scanned_at, '+00:00', ?))) as days_count", [$this->tzOffset($timezone)])
            ->value('days_count');
        $schoolDays = max($schoolDays, 1);

        // Get all entry attendance logs in the range for these students
        $logs = \App\Models\AttendanceLog::where('school_id', $schoolId)
            ->where('type', 'in')
            ->whereIn('student_id', $studentIds)
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
        $gradeAgg = [];
        $groupAgg = [];

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

        // Get the earliest attendance record date for the teacher's students (for calendar min date)
        $earliestLog = \App\Models\AttendanceLog::where('school_id', $schoolId)
            ->where('type', 'in')
            ->whereIn('student_id', $studentIds)
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
