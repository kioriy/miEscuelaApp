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
        Schema::table('teacher_group_assignments', function (Blueprint $table) {
            $table->unique(['user_id', 'classroom_id'], 'unique_teacher_classroom');
            $table->dropUnique('unique_teacher_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_group_assignments', function (Blueprint $table) {
            $table->unique(['user_id', 'school_level', 'shift'], 'unique_teacher_group');
            $table->dropUnique('unique_teacher_classroom');
        });
    }
};
