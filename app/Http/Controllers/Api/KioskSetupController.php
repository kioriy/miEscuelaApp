<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kiosk;
use App\Models\School;

class KioskSetupController extends Controller
{
    /**
     * Valida el PIN de activación en la tablet y vincula el dispositivo a la escuela.
     */
    public function getStatus(Request $request)
    {
        $user = $request->user();
        $schoolId = $user ? $user->school_id : $request->query('school_id');

        if (!$schoolId) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo determinar la escuela.'
            ], 400);
        }

        $school = School::find($schoolId);
        if (!$school) {
            return response()->json([
                'success' => false,
                'message' => 'Escuela no encontrada.'
            ], 404);
        }

        $activeKiosksCount = $school->kiosks()->where('is_active', true)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'active_count' => $activeKiosksCount,
                'total_allowed' => $school->allowed_kiosks,
            ]
        ]);
    }

    public function activate(Request $request)
    {
        $request->validate([
            'activation_code' => 'required|string|max:20',
            'device_name' => 'nullable|string|max:50',
        ]);

        $code = trim($request->activation_code);

        // 1. Buscamos el kiosco por PIN que NO esté activo
        $kiosk = Kiosk::where('activation_code', $code)->first();

        if (!$kiosk) {
            return response()->json([
                'success' => false,
                'message' => 'El código de activación es inválido o no existe.'
            ], 404);
        }

        if ($kiosk->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Este código ya ha sido utilizado en otro dispositivo.'
            ], 403);
        }

        // 2. Verificamos que la escuela no haya superado su límite
        $school = School::find($kiosk->school_id);

        $activeKiosksCount = $school->kiosks()->where('is_active', true)->count();

        if ($activeKiosksCount >= $school->allowed_kiosks) {
            return response()->json([
                'success' => false,
                'message' => 'Límite de kioscos alcanzado. Contacta a soporte para aumentar tu plan.'
            ], 403);
        }

        // 3. Activamos el Kiosco
        $kiosk->update([
            'is_active' => true,
            'name' => $request->device_name ?? 'Kiosco ' . ($activeKiosksCount + 1),
        ]);

        // 4. Generamos un token permanente (Sanctum) exclusivo para este Kiosco
        // Ojo: Usamos un naming diferenciado de tokens para mayor seguridad
        $apiToken = $kiosk->createToken('kiosk_monitor')->plainTextToken;

        // Lo guardamos en su base de datos para rastreo rápido
        $kiosk->update(['device_token' => hash('sha256', $apiToken)]);

        return response()->json([
            'success' => true,
            'message' => 'Dispositivo vinculado correctamente.',
            'token' => $apiToken,
            'kiosk' => [
                'id' => $kiosk->id,
                'name' => $kiosk->name,
                'school_id' => $school->id,
                'school_name' => $school->name,
                'school_logo_url' => $school->logo_path ? asset('storage/' . $school->logo_path) : null
            ]
        ], 200);
    }

    /**
     * Generates a short-lived signed token with the server's current timestamp
     * Needs to be called by an authenticated admin/director.
     */
    public function getTimeSyncToken(Request $request)
    {
        $payload = [
            'type' => 'SYNC_TIME',
            'timestamp' => now()->timestamp,
            'exp' => now()->addMinutes(15)->timestamp, // QR code expires in 15 mins
            'school_id' => $request->user()->school_id,
        ];

        // Add a simple signature so offline devices can do basic validation
        $payload['sig'] = hash('sha256', $payload['timestamp'] . 'miEscuelaOfflineSecret');

        // Encode as base64 so the offline frontend can decode it easily
        $token = base64_encode(json_encode($payload));

        return response()->json([
            'success' => true,
            'token' => $token,
            'expires_at' => $payload['exp']
        ]);
    }

    /**
     * Receives the signed token, calculates the offset and saves it.
     * Called by the Kiosk.
     */
    public function applyTimeOffset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'kiosk_local_timestamp' => 'required|numeric' // Timestamp from the kiosk when scanning
        ]);

        try {
            $payloadStr = base64_decode($request->token);
            $payload = json_decode($payloadStr, true);

            if (!isset($payload['timestamp']) || !isset($payload['exp'])) {
                throw new \Exception("Invalid token format");
            }

            if (now()->timestamp > $payload['exp']) {
                return response()->json([
                    'success' => false,
                    'message' => 'El código QR ha expirado. Por favor, genere uno nuevo.'
                ], 400);
            }

            // The user must be authenticated via Sanctum to their specific Kiosk
            $kioskToken = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            if (!$kioskToken) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 401);
            }

            $kiosk = \App\Models\Kiosk::find($kioskToken->tokenable_id);
            if (!$kiosk) {
                return response()->json(['success' => false, 'message' => 'Kiosco no encontrado'], 404);
            }

            // Optional: verify that the token belongs to the same school
            if (isset($payload['school_id']) && $kiosk->school_id !== $payload['school_id']) {
                return response()->json(['success' => false, 'message' => 'Escuela no coincide'], 403);
            }

            // Calculate offset: offset is how many seconds to ADD to the Kiosk's local time to get the Server's time.
            // Server Time: $payload['timestamp']
            // Kiosk Local Time: $request->kiosk_local_timestamp
            $offsetSeconds = (int)$payload['timestamp'] - (int)$request->kiosk_local_timestamp;

            $kiosk->update([
                'time_offset_seconds' => $offsetSeconds
            ]);

            return response()->json([
                'success' => true,
                'offset_seconds' => $offsetSeconds,
                'message' => 'Sincronización de hora exitosa.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aplicar sincronización de hora: ' . $e->getMessage()
            ], 500);
        }
    }
}
