<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kiosk;
use App\Models\School;
use App\Traits\HandlesSchoolContext;

class KioskSetupController extends Controller
{
    use HandlesSchoolContext;
    /**
     * Valida el PIN de activación en la tablet y vincula el dispositivo a la escuela.
     */
    public function getStatus(Request $request)
    {
        $schoolId = $this->getSchoolId($request);

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

        $activeKiosksCount = $school->ownedKiosks()->where('is_active', true)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'active_count' => $activeKiosksCount,
                'total_allowed' => $school->allowed_kiosks,
                'school_name' => $school->name,
            ]
        ]);
    }

    public function getSchoolsForActivation(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = auth('sanctum')->user();

        $query = School::select('id', 'name', 'allowed_kiosks')
            ->withCount(['ownedKiosks as active_kiosks' => function ($q) {
                $q->where('is_active', true);
            }]);

        if ($user && $user->role !== 'super_admin') {
            $schoolIds = $user->schools()->pluck('schools.id');
            $query->whereIn('id', $schoolIds);
        } else if (!$user) {
            // Si por alguna razón no hay usuario autenticado, retornamos vacío por seguridad
            $query->where('id', -1);
        }

        $schools = $query->get();

        return response()->json([
            'success' => true,
            'data' => $schools
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

        // 2. Si el kiosco YA está activo, permitir reactivación:
        //    Esto ocurre cuando la PWA pierde el token (ej. iOS limpia IndexedDB)
        //    pero el backend sigue con is_active = true.
        if ($kiosk->is_active) {
            // Revocar todos los tokens anteriores del kiosco
            $kiosk->tokens()->delete();

            // Actualizar nombre si se proporcionó
            if ($request->device_name) {
                $kiosk->update(['name' => $request->device_name]);
            }

            // Generar nuevo token
            $apiToken = $kiosk->createToken('kiosk_monitor')->plainTextToken;
            $kiosk->update(['device_token' => hash('sha256', $apiToken)]);

            return response()->json([
                'success' => true,
                'message' => 'Dispositivo reconectado correctamente.',
                'token' => $apiToken,
                'kiosk' => [
                    'id' => $kiosk->id,
                    'name' => $kiosk->name,
                    'schools' => $kiosk->load('schools')->schools->map(function ($s) {
                        return [
                            'id' => $s->id,
                            'name' => $s->name,
                            'logo_url' => $s->logo_path ? asset('storage/' . $s->logo_path) : null,
                            'qr_scan_type' => $s->qr_scan_type
                        ];
                    })
                ]
            ], 200);
        }

        // 3. Activamos el Kiosco por primera vez
        $kiosk->update([
            'is_active' => true,
            'name' => $request->device_name ?? $kiosk->name
        ]);

        // 4. Generamos un token permanente (Sanctum) exclusivo para este Kiosco
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
                'schools' => $kiosk->load('schools')->schools->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'name' => $s->name,
                        'logo_url' => $s->logo_path ? asset('storage/' . $s->logo_path) : null,
                        'qr_scan_type' => $s->qr_scan_type
                    ];
                })
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
            'school_id' => $this->getSchoolId($request),
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

            // Optional: verify that the token belongs to one of the schools this Kiosk manages
            // Note: Since Kiosks can handle multiple schools, we check if the requested sync school_id is allowed
            // We'll extract this by loading the relations
            $kiosk->load('schools');
            $allowedSchoolIds = $kiosk->schools->pluck('id')->toArray();

            if (isset($payload['school_id']) && !in_array($payload['school_id'], $allowedSchoolIds)) {
                return response()->json(['success' => false, 'message' => 'Escuela no autorizada para este kiosco'], 403);
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
