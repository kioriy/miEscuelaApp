<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classroom;
use App\Traits\HandlesSchoolContext;

class ClassroomController extends Controller
{
    use HandlesSchoolContext;

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId($request);

        $classrooms = Classroom::where('school_id', $schoolId)
            ->orderBy('school_level')
            ->orderBy('grade')
            ->orderBy('group_letter')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $classrooms
        ]);
    }
}
