<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ParentDashboardController extends Controller
{
    private function getStudentTimezone($student) {
        if ($student && $student->classroom && $student->classroom->school) {
            return $student->classroom->school->timezone ?? 'America/Mexico_City';
        }
        return 'America/Mexico_City';
    }

    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Fetch children matching the parent's email, loading the LATEST attendance log
        $children = Student::with(['classroom', 'attendanceLogs' => function ($query) {
            $query->with(['kiosk', 'recordedBy'])->latest('scanned_at');
        }])
            ->where(function ($query) use ($user) {
                $query->where('tutor_email', $user->email)
                    ->orWhere('secondary_tutor_email', $user->email);
            })
            ->get();

        $studentIds = $children->pluck('id')->toArray();

        // Attach last attendance log and current status for each child
        $childrenObj = $children->map(function ($child) {
            $lastLog = $child->attendanceLogs->first();
            $status = 'Desconocido';
            $mappedType = null;
            if ($lastLog) {
                $status = $lastLog->type === 'in' ? 'En la escuela' : 'En casa';
                $mappedType = $lastLog->type === 'in' ? 'entrada' : 'salida';
            }

            return [
                'id' => $child->id,
                'first_name' => $child->first_name,
                'last_name' => $child->last_name,
                'avatar' => $child->photo_path ? asset('storage/' . $child->photo_path) : 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($child->first_name . ' ' . $child->last_name),
                'classroom_label' => $child->classroom ? "{$child->classroom->grade} Grado - Grupo {$child->classroom->group_letter}" : "Sin Grupo",
                'status' => $status,
                'last_record' => $lastLog ? [
                    'type' => $mappedType, // entrada, salida
                    'time' => Carbon::parse($lastLog->scanned_at)->setTimezone($this->getStudentTimezone($child))->format('g:i A'),
                    'location' => $lastLog->kiosk ? $lastLog->kiosk->name : 'N/A',
                    'method' => $lastLog->recorded_by_user_id ? 'Asistencia por el profesor' : 'Escaneado con credencial'
                ] : null
            ];
        });

        // 2. Fetch Recent Activity (last 10 logs for these children)
        $recentActivity = AttendanceLog::with(['student', 'kiosk', 'recordedBy'])
            ->whereIn('student_id', $studentIds)
            ->orderBy('scanned_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($log) {
                // Ensure we use the school's timezone for the time string
                $timezone = 'America/Mexico_City';
                if ($log->student && $log->student->classroom && $log->student->classroom->school) {
                    $timezone = $log->student->classroom->school->timezone ?? 'America/Mexico_City';
                }
                
                $parsedTime = Carbon::parse($log->scanned_at)->setTimezone($timezone);
                $dayStr = $parsedTime->isToday() ? 'Hoy' : ($parsedTime->isYesterday() ? 'Ayer' : $parsedTime->format('d M'));
                $mappedType = $log->type === 'in' ? 'entrada' : 'salida';

                return [
                    'id' => $log->id,
                    'type' => $mappedType,
                    'time_label' => $dayStr . ', ' . $parsedTime->format('g:i A'),
                    'student_name' => $log->student->first_name . ' ' . $log->student->last_name,
                    'location' => $log->kiosk ? $log->kiosk->name : 'N/A',
                    'method' => $log->recorded_by_user_id ? 'Asistencia por el profesor' : 'Escaneado con credencial'
                ];
            });

        // 3. Fetch Announcements — only today's messages for the dashboard, scoped to the parent's school
        // Derive school_id from the parent's children (a parent belongs to one school)
        $parentSchoolId = $children->first()?->school_id ?? null;

        $announcementsQuery = DB::table('announcements')
            ->whereDate('announcements.created_at', now()->toDateString())
            ->orderBy('announcements.created_at', 'desc');

        if ($parentSchoolId) {
            $announcementsQuery->where('announcements.school_id', $parentSchoolId);
        }

        // Filter: show general (all_school) messages OR segmented messages that target this parent's children
        $announcementsQuery->where(function ($q) use ($studentIds, $children) {
            // Always show general school-wide announcements
            $q->where('announcements.is_general', true);

            // Also show segmented announcements that target one of this parent's children's groups or the child directly
            if (!empty($studentIds)) {
                $q->orWhereExists(function ($sub) use ($studentIds, $children) {
                    $sub->select(DB::raw(1))
                        ->from('announcement_targets')
                        ->whereColumn('announcement_targets.announcement_id', 'announcements.id')
                        ->where(function ($tq) use ($studentIds, $children) {
                            // Target is this specific child
                            $tq->whereIn('announcement_targets.student_id', $studentIds);

                            // OR target is a group/grade that contains one of this parent's children
                            foreach ($children as $child) {
                                if ($child->classroom_id) {
                                    $classroom = $child->classroom;
                                    if ($classroom) {
                                        $tq->orWhere(function ($gq) use ($classroom) {
                                            $gq->where('announcement_targets.grade', $classroom->grade)
                                               ->where('announcement_targets.group_letter', $classroom->group_letter)
                                               ->whereNull('announcement_targets.student_id');
                                        });
                                    }
                                }
                            }
                        });
                });
            }
        });

        $announcements = $announcementsQuery->get()->map(function ($ann) {
                $parsed = Carbon::parse($ann->created_at);
                $timeAgo = $parsed->diffForHumans();

                // Deterministic icon/color based on title
                $icon = 'calendarOutline';
                $color = 'blue';
                if (stripos($ann->title, 'salida') !== false || stripos($ann->title, 'aviso') !== false) {
                    $icon = 'warningOutline';
                    $color = 'orange';
                }

                return [
                    'id' => $ann->id,
                    'title' => $ann->title,
                    'message' => $ann->content,
                    'time_ago' => $timeAgo,
                    'icon' => $icon,
                    'color' => $color
                ];
            });

        // 4. Authorized Persons
        $childrenIds = collect($childrenObj)->pluck('id')->toArray();
        $authorizedPersonsRaw = DB::table('authorized_persons')
            ->whereIn('student_id', $childrenIds)
            ->get();

        // One person can be authorized for multiple students, group them by name/phone to avoid duplicates in UI
        $authorizedPersons = $authorizedPersonsRaw->unique(function ($item) {
            return $item->full_name . '-' . $item->relationship;
        })->map(function ($person) {
            return [
                'id' => $person->id,
                'name' => $person->full_name,
                'avatar' => $person->photo_path,
                'verified' => true, // Assuming true for now
                'relationship' => ucfirst($person->relationship),
                'id_snippet' => str_pad($person->id, 4, '0', STR_PAD_LEFT)
            ];
        })->values()->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'name' => $user->name,
                    'role_label' => 'Padre de Familia',
                    'avatar' => 'https://api.dicebear.com/7.x/adventurer/svg?seed=' . urlencode($user->name)
                ],
                'children' => $childrenObj,
                'recent_activity' => $recentActivity,
                'announcements' => $announcements,
                'authorized_persons' => $authorizedPersons
            ]
        ]);
    }

    /**
     * Get all announcements (paginated) for the full messages view.
     * Only returns announcements scoped to the parent's school, respecting targeting.
     */
    public function getAnnouncements(Request $request)
    {
        $user = $request->user();

        // Resolve the parent's children to determine school and classrooms
        $children = Student::with('classroom')
            ->where(function ($query) use ($user) {
                $query->where('tutor_email', $user->email)
                      ->orWhere('secondary_tutor_email', $user->email);
            })
            ->get();

        $studentIds    = $children->pluck('id')->toArray();
        $parentSchoolId = $children->first()?->school_id ?? null;

        $query = DB::table('announcements')
            ->orderBy('announcements.created_at', 'desc');

        // Restrict to this school only
        if ($parentSchoolId) {
            $query->where('announcements.school_id', $parentSchoolId);
        }

        // Show general announcements OR segmented ones that apply to this parent's children
        $query->where(function ($q) use ($studentIds, $children) {
            $q->where('announcements.is_general', true);

            if (!empty($studentIds)) {
                $q->orWhereExists(function ($sub) use ($studentIds, $children) {
                    $sub->select(DB::raw(1))
                        ->from('announcement_targets')
                        ->whereColumn('announcement_targets.announcement_id', 'announcements.id')
                        ->where(function ($tq) use ($studentIds, $children) {
                            $tq->whereIn('announcement_targets.student_id', $studentIds);

                            foreach ($children as $child) {
                                if ($child->classroom_id && $child->classroom) {
                                    $classroom = $child->classroom;
                                    $tq->orWhere(function ($gq) use ($classroom) {
                                        $gq->where('announcement_targets.grade', $classroom->grade)
                                           ->where('announcement_targets.group_letter', $classroom->group_letter)
                                           ->whereNull('announcement_targets.student_id');
                                    });
                                }
                            }
                        });
                });
            }
        });

        $announcements = $query->paginate(20);

        $announcements->getCollection()->transform(function ($ann) {
            $parsed = Carbon::parse($ann->created_at);

            $icon = 'calendarOutline';
            $color = 'blue';
            if (stripos($ann->title, 'salida') !== false || stripos($ann->title, 'aviso') !== false) {
                $icon = 'warningOutline';
                $color = 'orange';
            }

            return [
                'id'       => $ann->id,
                'title'    => $ann->title,
                'message'  => $ann->content,
                'time_ago' => $parsed->diffForHumans(),
                'date'     => $parsed->format('d M, Y'),
                'time'     => $parsed->format('g:i A'),
                'icon'     => $icon,
                'color'    => $color
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $announcements
        ]);
    }

    public function getStudents(Request $request)
    {
        $user = $request->user();

        $children = Student::with('classroom')
            ->where(function ($query) use ($user) {
                $query->where('tutor_email', $user->email)
                    ->orWhere('secondary_tutor_email', $user->email);
            })
            ->get()
            ->map(function ($child) {
                $gradeString = $child->classroom ? "{$child->classroom->grade} Grado" : "Sin Grado";
                $groupString = $child->classroom && $child->classroom->group_letter ? " - Grupo {$child->classroom->group_letter}" : "";

                return [
                    'id' => $child->id,
                    'full_name' => "{$child->first_name} {$child->last_name}",
                    'classroom_label' => "{$gradeString}{$groupString}"
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $children
        ]);
    }

    public function storeAuthorizedPerson(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'relationship' => 'required|string|max:50',
            'phone' => 'nullable|string|max:20',
            'student_ids' => 'required|string', // Assuming comma-separated array or JSON string from FormData
            'photo' => 'nullable|file|mimes:jpeg,png|max:5120' // max 5MB
        ]);

        $user = $request->user();

        // Parse student_ids
        $studentIds = json_decode($request->student_ids, true) ?? [];
        if (!is_array($studentIds)) {
            $studentIds = [$request->student_ids];
        }

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Storing in public disk under 'authorized_photos'
            $path = $file->storeAs('authorized_photos', $filename, 'public');
            $photoPath = '/storage/' . $path;
        }

        // Validate that requested students belong to parent
        $validStudentIds = Student::where(function ($query) use ($user) {
            $query->where('tutor_email', $user->email)
                ->orWhere('secondary_tutor_email', $user->email);
        })
            ->whereIn('id', $studentIds)
            ->pluck('id')
            ->toArray();

        if (empty($validStudentIds)) {
            return response()->json(['success' => false, 'message' => 'No valid students selected.'], 400);
        }

        $now = now();
        $inserts = [];

        foreach ($validStudentIds as $sid) {
            $inserts[] = [
                'student_id' => $sid,
                'full_name' => $request->name,
                'relationship' => $request->relationship,
                'phone' => $request->phone,
                'photo_path' => $photoPath,
                'is_primary' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('authorized_persons')->insert($inserts);

        return response()->json([
            'success' => true,
            'message' => 'Autorización guardada correctamente.'
        ]);
    }

    public function getHistory(Request $request)
    {
        $user = $request->user();

        // Obtener a los alumnos de este padre
        $children = Student::where(function ($query) use ($user) {
            $query->where('tutor_email', $user->email)
                ->orWhere('secondary_tutor_email', $user->email);
        })->get();

        $studentIds = $children->pluck('id')->toArray();

        // Filtro opcional por un alumno específico
        $filterStudentId = $request->query('student_id');
        if ($filterStudentId && in_array($filterStudentId, $studentIds)) {
            $studentIds = [$filterStudentId];
        }

        // --- Calcular Metricas del Mes ---
        $now = now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();

        $prevMonthStart = $now->copy()->subMonth()->startOfMonth();
        $prevMonthEnd = $now->copy()->subMonth()->endOfMonth();

        $currentMonthLogs = AttendanceLog::whereIn('student_id', $studentIds)
            ->where('type', 'in')
            ->whereBetween('scanned_at', [$currentMonthStart, $currentMonthEnd])
            ->get();

        $prevMonthLogs = AttendanceLog::whereIn('student_id', $studentIds)
            ->where('type', 'in')
            ->whereBetween('scanned_at', [$prevMonthStart, $prevMonthEnd])
            ->get();

        $currentAttendances = $currentMonthLogs->whereIn('status', ['present', 'late'])->count();
        $prevAttendances = $prevMonthLogs->whereIn('status', ['present', 'late'])->count();

        $currentLates = $currentMonthLogs->where('status', 'late')->count();
        $prevLates = $prevMonthLogs->where('status', 'late')->count();

        $currentPunctuality = $currentAttendances > 0 ? round((($currentAttendances - $currentLates) / $currentAttendances) * 100) : 100;
        $prevPunctuality = $prevAttendances > 0 ? round((($prevAttendances - $prevLates) / $prevAttendances) * 100) : 100;

        $attendanceDelta = $currentAttendances - $prevAttendances;
        $punctualityDelta = $currentPunctuality - $prevPunctuality;
        $latesDelta = $currentLates - $prevLates;

        $stats = [
            'attendances' => [
                'value' => $currentAttendances,
                'trend' => $attendanceDelta >= 0 ? "+{$attendanceDelta} vs mes anterior" : "{$attendanceDelta} vs mes anterior",
                'is_positive' => $attendanceDelta >= 0
            ],
            'punctuality' => [
                'value' => $currentPunctuality . '%',
                'trend' => $punctualityDelta >= 0 ? "+{$punctualityDelta}% vs mes anterior" : "{$punctualityDelta}% vs mes anterior",
                'is_positive' => $punctualityDelta >= 0
            ],
            'lates' => [
                'value' => $currentLates,
                'trend' => $latesDelta <= 0 ? (abs($latesDelta) . " menos lates") : "+{$latesDelta} más lates",
                'is_positive' => $latesDelta <= 0
            ]
        ];

        // Obtener historial paginado
        $logs = AttendanceLog::with(['student', 'kiosk', 'recordedBy'])
            ->whereIn('student_id', $studentIds)
            ->orderBy('scanned_at', 'desc')
            ->paginate(20);

        // Transformar la colección conservando la paginación
        $logs->getCollection()->transform(function ($log) {
            $timezone = 'America/Mexico_City';
            if ($log->student && $log->student->classroom && $log->student->classroom->school) {
                $timezone = $log->student->classroom->school->timezone ?? 'America/Mexico_City';
            }
            $parsedTime = Carbon::parse($log->scanned_at)->setTimezone($timezone);
            $dayStr = $parsedTime->isToday() ? 'Hoy' : ($parsedTime->isYesterday() ? 'Ayer' : $parsedTime->format('d M, Y'));
            $mappedType = $log->type === 'in' ? 'entrada' : 'salida';

            return [
                'id' => $log->id,
                'type' => $mappedType,
                'time_label' => $dayStr . ', ' . $parsedTime->format('g:i A'),
                'student_name' => $log->student->first_name . ' ' . $log->student->last_name,
                'avatar' => $log->student->photo_path ? asset('storage/' . $log->student->photo_path) : 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($log->student->first_name . ' ' . $log->student->last_name),
                'location' => $log->kiosk ? $log->kiosk->name : 'N/A',
                'method' => $log->recorded_by_user_id ? 'Asistencia por el profesor' : 'Escaneado con credencial'
            ];
        });

        // Retornar respuesta
        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'children' => $children->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->first_name . ' ' . $child->last_name
                    ];
                }),
                'history' => $logs
            ]
        ]);
    }
}
