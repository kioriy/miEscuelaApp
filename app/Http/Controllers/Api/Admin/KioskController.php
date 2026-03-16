<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kiosk;
use App\Models\School;
use App\Traits\HandlesSchoolContext;

class KioskController extends Controller
{
    use HandlesSchoolContext;

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId($request);

        $query = Kiosk::with(['ownerSchool', 'schools']);

        if ($schoolId && $request->user()->role !== 'super_admin') {
            $query->where('owner_school_id', $schoolId)
                ->orWhereHas('schools', function ($q) use ($schoolId) {
                    $q->where('schools.id', $schoolId);
                });
        }

        $kiosks = $query->get();
        return response()->json(['success' => true, 'data' => $kiosks]);
    }

    public function show($id)
    {
        $kiosk = Kiosk::with(['ownerSchool', 'schools'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $kiosk]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'owner_school_id' => 'required|exists:schools,id'
        ]);
        $kiosk = Kiosk::create([
            'name' => $request->name,
            'owner_school_id' => $request->owner_school_id,
            'activation_code' => 'K-' . strtoupper(substr(uniqid(), -4)) . '-' . mt_rand(10, 99)
        ]);

        // Automatically link the owner school
        $kiosk->schools()->attach($request->owner_school_id);

        return response()->json(['success' => true, 'data' => $kiosk->load(['ownerSchool', 'schools'])]);
    }

    public function update(Request $request, $id)
    {
        $kiosk = Kiosk::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'owner_school_id' => 'nullable|exists:schools,id',
        ]);

        $oldOwnerSchoolId = $kiosk->owner_school_id;

        $kiosk->update([
            'name' => $request->name,
        ]);

        if ($request->filled('owner_school_id') && $request->owner_school_id != $oldOwnerSchoolId) {
            $kiosk->update(['owner_school_id' => $request->owner_school_id]);
            // Link the new owner school if it's not already linked
            if (!$kiosk->schools()->where('school_id', $request->owner_school_id)->exists()) {
                $kiosk->schools()->attach($request->owner_school_id);
            }
        }

        return response()->json(['success' => true, 'data' => $kiosk->load(['ownerSchool', 'schools'])]);
    }

    public function destroy($id)
    {
        $kiosk = Kiosk::findOrFail($id);
        $kiosk->schools()->detach();
        $kiosk->delete();
        return response()->json(['success' => true, 'message' => 'Kiosco eliminado exitosamente']);
    }

    public function linkSchool(Request $request, $id)
    {
        $request->validate(['school_id' => 'required|exists:schools,id']);
        $kiosk = Kiosk::findOrFail($id);

        if (!$kiosk->schools()->where('school_id', $request->school_id)->exists()) {
            $kiosk->schools()->attach($request->school_id);
        }

        return response()->json(['success' => true, 'data' => $kiosk->load(['schools'])]);
    }

    public function unlinkSchool(Request $request, $id)
    {
        $request->validate(['school_id' => 'required|exists:schools,id']);
        $kiosk = Kiosk::findOrFail($id);

        // Don't allow unlinking the owner school
        if ($kiosk->owner_school_id == $request->school_id) {
            return response()->json(['success' => false, 'message' => 'No se puede desvincular la escuela propietaria'], 400);
        }

        $kiosk->schools()->detach($request->school_id);

        return response()->json(['success' => true, 'data' => $kiosk->load(['schools'])]);
    }

    public function reset($id)
    {
        $kiosk = Kiosk::findOrFail($id);

        // Delete all sanctum tokens for this kiosk to force logout on the physical device
        $kiosk->tokens()->delete();

        // Reset the Kiosk back to its offline, unlinked state while preserving its schools and owner
        $kiosk->update([
            'is_active' => false,
            'device_token' => null,
            'name' => null // Optional: clear the custom name it grabbed during activation
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'El Kiosko ha sido desvinculado exitosamente.',
            'data' => $kiosk->load(['ownerSchool', 'schools'])
        ]);
    }
}
