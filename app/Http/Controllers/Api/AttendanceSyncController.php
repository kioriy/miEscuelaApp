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
            'logs' => 'required|array',
            'logs.*.student_id' => 'required|integer',
            'logs.*.scanned_at' => 'required|date',
            'logs.*.type' => 'required|in:in,out',
            // device_id y authorized_person_id son opcionales
        ]);

        // 2. Obtenemos el ID general de escuela y del kiosco desde el token
        $user = $request->user();
        $fallbackSchoolId = $this->getSchoolId($request);
        $kioskId = ($user instanceof \App\Models\Kiosk) ? $user->id : null;

        $logs = $request->input('logs');
        $studentIds = collect($logs)->pluck('student_id')->unique()->toArray();

        // Pre-fetch la escuela primigenia de todos los estudiantes para enrutarlos correctamente (Multi-Sede)
        $studentSchools = \App\Models\Student::whereIn('id', $studentIds)
            ->pluck('school_id', 'id')
            ->toArray();

        $recordsToInsert = [];
        $now = now(); // Native Carbon object

        // 3. Preparamos los datos
        foreach ($logs as $log) {
            // Convertimos la fecha ISO (JS) a objeto Carbon
            $scannedAt = \Illuminate\Support\Carbon::parse($log['scanned_at']);

            // Buscamos a cuál de las sedes pertenece este alumno; si no se encuentra (borrado), usamos el fallback
            $resolvedSchoolId = $studentSchools[$log['student_id']] ?? $fallbackSchoolId;

            $recordsToInsert[] = [
                'school_id' => $resolvedSchoolId,
                'student_id' => $log['student_id'],
                'scanned_at' => $scannedAt,
                'type' => $log['type'],
                'kiosk_id' => $log['kiosk_id'] ?? $kioskId,
                'recorded_by_user_id' => ($user instanceof \App\Models\User) ? $user->id : null,
                'authorized_person_id' => $log['authorized_person_id'] ?? null,
                'sync_status' => 'synced',
                'created_at' => $now
            ];
        }

        // 4. Inserción Masiva (Bulk Insert) dentro de una Transacción
        // Las transacciones aseguran que si hay un error en el registro 50, no se guarde nada a medias.
        try {
            DB::beginTransaction();

            // Insertamos todo el arreglo de un solo golpe. ¡Esto es súper rápido en MySQL!
            AttendanceLog::insert($recordsToInsert);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($recordsToInsert) . ' registros sincronizados correctamente.',
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
