<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceSyncController extends Controller
{
    /**
     * Obtiene estadísticas rápidas de hoy para el monitor.
     */
    public function getKioskStats(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $today = now()->startOfDay();

        $entries = AttendanceLog::where('school_id', $schoolId)
            ->where('scanned_at', '>=', $today)
            ->where('type', 'in')
            ->count();

        $exits = AttendanceLog::where('school_id', $schoolId)
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

        // 2. Obtenemos el ID de la escuela y del kiosco desde el token
        $user = $request->user();
        $schoolId = $user->school_id;
        $kioskId = ($user instanceof \App\Models\Kiosk) ? $user->id : null;

        $logs = $request->input('logs');

        $recordsToInsert = [];
        $now = now()->toDateTimeString(); // Momento exacto en que llega al servidor

        // 3. Preparamos los datos
        foreach ($logs as $log) {
            // Convertimos la fecha ISO (JS) a formato MySQL
            $scannedAt = \Illuminate\Support\Carbon::parse($log['scanned_at'])->toDateTimeString();

            $recordsToInsert[] = [
                'school_id' => $schoolId,
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
