<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\AuthorizedPerson;
use App\Traits\HandlesSchoolContext;

class MonitorSyncController extends Controller
{
    use HandlesSchoolContext;
    /**
     * Descarga incremental de Alumnos y Autorizados para el Monitor Offline.
     */
    public function pullData(Request $request)
    {
        // 1. Obtenemos la fecha de la última sincronización. 
        // Si es la primera vez (null), ponemos una fecha muy antigua para descargar todo.
        $lastSync = $request->input('last_sync', '2000-01-01 00:00:00');

        // 2. Obtenemos el o los IDs de las escuelas y detectamos cuáles son nuevas.
        $user = $request->user();
        $standardSchoolIds = [];
        $newSchoolIds = [];

        if ($user instanceof \App\Models\Kiosk) {
            $user->load('schools');
            $kiosk = $user;
        } else {
            // Fallback for Director/Admin previewing or testing
            $kioskId = $request->input('kiosk_id');
            if ($kioskId) {
                // Validate if user has access to this kiosk (simplified for now, usually check school_id)
                $kiosk = \App\Models\Kiosk::with('schools')->find($kioskId);
            } else {
                $kiosk = null;
            }
        }

        if ($kiosk) {
            $now = now();
            // Heartbeat update
            $kiosk->update(['last_sync_at' => $now]);
            
            foreach ($kiosk->schools as $school) {
                $pivotCreatedAt = $school->pivot->created_at;

                // Safety Window: If affiliated in the last 24 hours (or missing timestamp), treat as NEW.
                $isRecentlyAffiliated = $pivotCreatedAt && $pivotCreatedAt->diffInHours($now) < 24;

                if (!$pivotCreatedAt || $isRecentlyAffiliated || $pivotCreatedAt > $lastSync) {
                    $newSchoolIds[] = $school->id;
                } else {
                    $standardSchoolIds[] = $school->id;
                }
            }
        } else {
            // Fallback to single school context if no kiosk identified
            $standardSchoolIds[] = $this->getSchoolId($request);
        }

        // 3. Buscamos los alumnos
        $students = Student::with('classroom')->where(function ($query) use ($standardSchoolIds, $newSchoolIds, $lastSync) {
            if (!empty($standardSchoolIds)) {
                $query->whereIn('school_id', $standardSchoolIds)->where('updated_at', '>', $lastSync);
            }
            if (!empty($newSchoolIds)) {
                $query->orWhereIn('school_id', $newSchoolIds);
            }
        })
            ->select('id', 'school_id', 'classroom_id', 'enrollment_code', 'first_name', 'last_name', 'photo_path', 'photo_hash', 'is_active', 'updated_at')
            ->get();

        // 4. Formateamos las URLs de las fotos y anexamos grado/grupo
        $students = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'school_id' => $student->school_id,
                'enrollment_code' => $student->enrollment_code,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'grade' => $student->classroom ? $student->classroom->grade : null,
                'group_letter' => $student->classroom ? $student->classroom->group_letter : null,
                'photo_url' => $student->photo_path ? asset('storage/' . $student->photo_path) : null,
                'photo_hash' => $student->photo_hash,
                'is_active' => $student->is_active,
                'updated_at' => $student->updated_at,
            ];
        });

        // 5. Buscamos a las personas autorizadas relacionadas a estas escuelas
        $authorizedPersons = AuthorizedPerson::whereHas('student', function ($query) use ($standardSchoolIds, $newSchoolIds, $lastSync) {
            $query->where(function ($q) use ($standardSchoolIds, $newSchoolIds, $lastSync) {
                if (!empty($standardSchoolIds)) {
                    $q->whereIn('school_id', $standardSchoolIds);
                }
                if (!empty($newSchoolIds)) {
                    $q->orWhereIn('school_id', $newSchoolIds);
                }
            });
        })
            ->where(function ($query) use ($standardSchoolIds, $newSchoolIds, $lastSync) {
                $query->where('updated_at', '>', $lastSync)
                    ->orWhereHas('student', function ($q) use ($newSchoolIds) {
                        if (!empty($newSchoolIds)) {
                            $q->whereIn('school_id', $newSchoolIds);
                        } else {
                            $q->whereRaw('1 = 0');
                        }
                    });
            })
            ->select('id', 'student_id', 'full_name', 'relationship', 'photo_path', 'photo_hash', 'is_primary', 'updated_at')
            ->get();

        // 6. Formateamos las URLs de las fotos de los autorizados
        $authorizedPersons->transform(function ($person) {
            $person->photo_url = $person->photo_path ? asset('storage/' . $person->photo_path) : null;
            return $person;
        });

        // 7. Retornamos la información estructurada
        return response()->json([
            'success' => true,
            'timestamp' => now()->toDateTimeString(), // El monitor guardará esta fecha para su próxima petición
            'data' => [
                'students' => $students,
                'authorized_persons' => $authorizedPersons,
                'debug' => [
                    'received_kiosk_id' => $request->input('kiosk_id'),
                    'standardSchoolIds' => $standardSchoolIds,
                    'newSchoolIds' => $newSchoolIds
                ]
            ]
        ], 200);
    }
    public function getSchoolInfo(Request $request)
    {
        $user = $request->user();

        if ($user instanceof \App\Models\Kiosk) {
            $user->load('schools');
            $schools = $user->schools;

            if ($schools->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El kiosco no tiene escuelas asignadas.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'kiosk_name' => $user->name,
                    // Return array of schools instead of single
                    'schools' => $schools->map(function ($s) {
                        return [
                            'id' => $s->id,
                            'name' => $s->name,
                            'logo_url' => $s->logo_path ? asset('storage/' . $s->logo_path) : null,
                            'qr_scan_type' => $s->qr_scan_type
                        ];
                    })
                ]
            ]);
        }

        // Fallback for Director/Admin direct testing
        $schoolId = $this->getSchoolId($request);
        $school = \App\Models\School::find($schoolId);

        if (!$school) {
            return response()->json([
                'success' => false,
                'message' => 'Escuela no encontrada.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'kiosk_name' => 'Monitor Principal',
                'schools' => [[
                    'id' => $school->id,
                    'name' => $school->name,
                    'logo_url' => $school->logo_path ? asset('storage/' . $school->logo_path) : null,
                    'qr_scan_type' => $school->qr_scan_type
                ]]
            ]
        ]);
    }
}
