<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add classroom_id
        Schema::table('teacher_group_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('classroom_id')->nullable()->after('user_id');
        });

        // 2. Migrate existing data
        $assignments = \Illuminate\Support\Facades\DB::table('teacher_group_assignments')->get();
        foreach ($assignments as $assignment) {
            $user = \Illuminate\Support\Facades\DB::table('users')->where('id', $assignment->user_id)->first();
            if ($user) {
                $school_id = $user->school_id;
                if (!$school_id) {
                    $pivot = \Illuminate\Support\Facades\DB::table('school_user')->where('user_id', $user->id)->first();
                    $school_id = $pivot ? $pivot->school_id : null;
                }

                if ($school_id) {
                    $classroom = \Illuminate\Support\Facades\DB::table('classrooms')->where([
                        'school_id' => $school_id,
                        'grade' => $assignment->grade,
                        'group_letter' => $assignment->group_letter,
                    ])->first();

                    if (!$classroom) {
                        $classroomId = \Illuminate\Support\Facades\DB::table('classrooms')->insertGetId([
                            'school_id' => $school_id,
                            'school_level' => 'Primaria', // default
                            'grade' => $assignment->grade,
                            'group_letter' => $assignment->group_letter,
                            'shift' => 'matutino',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $classroomId = $classroom->id;
                    }

                    \Illuminate\Support\Facades\DB::table('teacher_group_assignments')
                        ->where('id', $assignment->id)->update(['classroom_id' => $classroomId]);
                }
            }
        }

        // 3. Add foreign key and drop old columns
        Schema::table('teacher_group_assignments', function (Blueprint $table) {
            $table->foreign('classroom_id')->references('id')->on('classrooms')->cascadeOnDelete();
            $table->dropColumn(['grade', 'group_letter']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_group_assignments', function (Blueprint $table) {
            $table->string('grade', 20)->nullable();
            $table->string('group_letter', 20)->nullable();
        });

        $assignments = \Illuminate\Support\Facades\DB::table('teacher_group_assignments')->get();
        foreach ($assignments as $assignment) {
            if ($assignment->classroom_id) {
                $classroom = \Illuminate\Support\Facades\DB::table('classrooms')->where('id', $assignment->classroom_id)->first();
                if ($classroom) {
                    \Illuminate\Support\Facades\DB::table('teacher_group_assignments')->where('id', $assignment->id)->update([
                        'grade' => $classroom->grade,
                        'group_letter' => $classroom->group_letter,
                    ]);
                }
            }
        }

        Schema::table('teacher_group_assignments', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->dropColumn('classroom_id');
        });
    }
};
