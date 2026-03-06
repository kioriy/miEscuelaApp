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
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedBigInteger('classroom_id')->nullable()->after('school_id');
        });

        // 2. Migrate existing data
        $students = \Illuminate\Support\Facades\DB::table('students')->get();
        foreach ($students as $student) {
            if (isset($student->school_level) && isset($student->grade) && isset($student->group_letter)) {
                $classroom = \Illuminate\Support\Facades\DB::table('classrooms')->where([
                    'school_id' => $student->school_id,
                    'school_level' => $student->school_level,
                    'grade' => $student->grade,
                    'group_letter' => $student->group_letter,
                    'shift' => $student->shift ?? 'matutino',
                ])->first();

                if (!$classroom) {
                    $classroomId = \Illuminate\Support\Facades\DB::table('classrooms')->insertGetId([
                        'school_id' => $student->school_id,
                        'school_level' => $student->school_level,
                        'grade' => $student->grade,
                        'group_letter' => $student->group_letter,
                        'shift' => $student->shift ?? 'matutino',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $classroomId = $classroom->id;
                }

                \Illuminate\Support\Facades\DB::table('students')->where('id', $student->id)->update(['classroom_id' => $classroomId]);
            }
        }

        // 3. Add foreign key and drop old columns
        Schema::table('students', function (Blueprint $table) {
            $table->foreign('classroom_id')->references('id')->on('classrooms')->nullOnDelete();
            $table->dropColumn(['school_level', 'grade', 'group_letter', 'shift']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('school_level', 50)->nullable();
            $table->string('grade', 20)->nullable();
            $table->string('group_letter', 20)->nullable();
            $table->enum('shift', ['matutino', 'vespertino', 'mixto'])->default('matutino');
        });

        $students = \Illuminate\Support\Facades\DB::table('students')->get();
        foreach ($students as $student) {
            if ($student->classroom_id) {
                $classroom = \Illuminate\Support\Facades\DB::table('classrooms')->where('id', $student->classroom_id)->first();
                if ($classroom) {
                    \Illuminate\Support\Facades\DB::table('students')->where('id', $student->id)->update([
                        'school_level' => $classroom->school_level,
                        'grade' => $classroom->grade,
                        'group_letter' => $classroom->group_letter,
                        'shift' => $classroom->shift,
                    ]);
                }
            }
        }

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->dropColumn('classroom_id');
        });
    }
};
