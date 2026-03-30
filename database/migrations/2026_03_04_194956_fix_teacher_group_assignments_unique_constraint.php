<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_group_assignments', function (Blueprint $table) {
            $table->unique(['user_id', 'classroom_id'], 'unique_teacher_classroom');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_group_assignments', function (Blueprint $table) {
            $table->dropUnique('unique_teacher_classroom');
        });
    }
};
