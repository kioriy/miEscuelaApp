<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\HandlesSchoolContext;

class AttendanceSyncController extends Controller
{
    use HandlesSchoolContext;
    /**
     * Obtiene estadísticas rápidas de hoy para el monitor.
     */
    public function getKioskStats(Request $request)
    {
        $user = $request->user();
        if ($user instanceof \App\Models\Kiosk) {
            $user->load('schools');
            $user->update(['last_sync_at' => now()]);
            $schoolIds = $user->schools->pluck('id')->toArray();
        } else {
            $schoolIds = [$this->getSchoolId($request)];
        }

        // Si el monitor envía su inicio de día local (ISO), lo usamos para filtrar exactamente lo mismo
        $localStart = $request->query('local_start');

        if ($localStart) {
            $today = \Illuminate\Support\Carbon::parse($localStart)->toDateTimeString();
        } else {
            // Fallback: inicio del día según servidor
            $today = now()->startOfDay();
        }

        $entries = AttendanceLog::whereIn('school_id', $schoolIds)
            ->where('scanned_at', '>=', $today)
            ->where('type', 'in')
            ->count();

        $exits = AttendanceLog::whereIn('school_id', $schoolIds)
            ->where('scanned_at', '>=', $today)
            ->where('type', 'out')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'entries' => $entries,
                'exits' => $exits,
                'total' => $entries + $exits
            ]
        ]);
    }

    /**
     * Recibe un lote (array) de asistencias escaneadas offline y las guarda de golpe.
     */
    public function pushData(Request $request)
    {
        // 1. Validamos que nos envíen un arreglo llamado "logs"
        $request->validate([
            'logs' => 'nullable|array',
            'logs.*.student_id' => 'required|integer',
            'logs.*.scanned_at' => 'required|date',
            'logs.*.type' => 'required|in:in,out',
            'teacher_logs' => 'nullable|array',
            'teacher_logs.*.user_id' => 'required|integer',
            'teacher_logs.*.scanned_at' => 'required|date',
            'teacher_logs.*.type' => 'required|in:in,out',
        ]);

        // 2. Obtenemos el ID general de escuela y del kiosco desde el token
        $user = $request->user();
        $fallbackSchoolId = $this->getSchoolId($request);
        
        $kioskId = null;
        if ($user instanceof \App\Models\Kiosk) {
            $kioskId = $user->id;
            $user->update(['last_sync_at' => now()]);
        }

        $logs = $request->input('logs', []);
        $teacherLogsRaw = $request->input('teacher_logs', []);
        
        $studentIds = collect($logs)->pluck('student_id')->unique()->toArray();
        $teacherUserIds = collect($teacherLogsRaw)->pluck('user_id')->unique()->toArray();

        // Pre-fetch la escuela primigenia de todos los estudiantes para enrutarlos correctamente (Multi-Sede)
        $studentSchools = \App\Models\Student::whereIn('id', $studentIds)
            ->pluck('school_id', 'id')
            ->toArray();
            
        $teacherSchools = \App\Models\User::whereIn('id', $teacherUserIds)
            ->pluck('school_id', 'id')
            ->toArray();

        $recordsToInsert = [];
        $now = now(); // Native Carbon object

        // Pre-fetch school settings to avoid N+1 querying during bulk inserts
        $allAffectedSchoolIds = array_unique(array_merge(
            array_values($studentSchools), 
            array_values($teacherSchools),
            [$fallbackSchoolId]
        ));

        $schoolSettings = \App\Models\School::whereIn('id', $allAffectedSchoolIds)
            ->get(['id', 'timezone', 'entry_time', 'tolerance_minutes'])
            ->keyBy('id');

        // 3. Preparamos los datos
        foreach ($logs as $log) {
            // Buscamos a cuál de las sedes pertenece este alumno; si no se encuentra (borrado), usamos el fallback
            $resolvedSchoolId = $studentSchools[$log['student_id']] ?? $fallbackSchoolId;
            $school = $schoolSettings->get($resolvedSchoolId);
            
            $schoolTimezone = $school->timezone ?? config('app.timezone', 'America/Mexico_City');
            $entryTime = $school->entry_time ?? '07:00:00';
            $tolerance = $school->tolerance_minutes ?? 15;

            // Convertimos la fecha ISO (JS) a objeto Carbon y la rotamos a la zona horaria real local
            // antes de blindarla en la DB (donde Laravel interpretará todas como 'America/Mexico_City' nativamente).
            $scannedAt = \Illuminate\Support\Carbon::parse($log['scanned_at'])->setTimezone($schoolTimezone);

            // Calcular estatus (presente o tarde) para entradas
            $status = 'present'; 
            if ($log['type'] === 'in') {
                // Creamos el límite de tiempo para ese día específico
                $limit = $scannedAt->copy()->setTimeFromTimeString($entryTime)->addMinutes($tolerance);
                if ($scannedAt->greaterThan($limit)) {
                    $status = 'late';
                }
            }

            $recordsToInsert[] = [
                'school_id' => $resolvedSchoolId,
                'student_id' => $log['student_id'],
                'scanned_at' => $scannedAt,
                'type' => $log['type'],
                'status' => $status,
                'kiosk_id' => $log['kiosk_id'] ?? $kioskId,
                'recorded_by_user_id' => ($user instanceof \App\Models\User) ? $user->id : null,
                'authorized_person_id' => $log['authorized_person_id'] ?? null,
                'sync_status' => 'synced',
                'created_at' => $now
            ];
        }

        // 3.1 Preparamos los datos de profesores
        $teacherRecordsToInsert = [];
        foreach ($teacherLogsRaw as $tlog) {
            $resolvedSchoolId = $teacherSchools[$tlog['user_id']] ?? $fallbackSchoolId;
            $school = $schoolSettings->get($resolvedSchoolId);
            
            $schoolTimezone = $school->timezone ?? config('app.timezone', 'America/Mexico_City');
            $entryTime = $school->entry_time ?? '07:00:00';
            $tolerance = $school->tolerance_minutes ?? 15;

            $scannedAt = \Illuminate\Support\Carbon::parse($tlog['scanned_at'])->setTimezone($schoolTimezone);

            $status = 'present';
            if ($tlog['type'] === 'in') {
                $limit = $scannedAt->copy()->setTimeFromTimeString($entryTime)->addMinutes($tolerance);
                if ($scannedAt->greaterThan($limit)) {
                    $status = 'late';
                }
            }

            $teacherRecordsToInsert[] = [
                'school_id' => $resolvedSchoolId,
                'user_id' => $tlog['user_id'],
                'scanned_at' => $scannedAt,
                'type' => $tlog['type'],
                'status' => $status,
                'kiosk_id' => $tlog['kiosk_id'] ?? $kioskId,
                'sync_status' => 'synced',
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        // 4. Inserción Masiva (Bulk Insert) dentro de una Transacción
        // Las transacciones aseguran que si hay un error en el registro 50, no se guarde nada a medias.
        try {
            DB::beginTransaction();

            // Insertamos los alumnos
            if (!empty($recordsToInsert)) {
                AttendanceLog::insert($recordsToInsert);
            }
            
            // Insertamos los profesores
            if (!empty($teacherRecordsToInsert)) {
                \App\Models\TeacherAttendanceLog::insert($teacherRecordsToInsert);
            }

            DB::commit();

            $totalSynced = count($recordsToInsert) + count($teacherRecordsToInsert);
            return response()->json([
                'success' => true,
                'message' => $totalSynced . ' registros sincronizados correctamente.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Si algo falla, deshacemos los cambios

            // Guardamos el error en los logs de Laravel para poder revisarlo después
            Log::error('Error en sincronización masiva: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Hubo un error al guardar los registros en el servidor.',
            ], 500);
        }
    }
}
