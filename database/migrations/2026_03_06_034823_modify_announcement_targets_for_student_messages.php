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
        Schema::table('announcement_targets', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->nullable()->after('announcement_id');

            $table->string('school_level', 50)->nullable()->change();
            $table->string('grade', 20)->nullable()->change();
            $table->string('group_letter', 20)->nullable()->change();
            // Enum cannot easily be changed directly via doctrine/dbal in some SQLite/MySQL setups if not careful, 
            // but Laravel 11/MySQL 8 handles string modification fine. Let's do string/enum nullable.
            // Using DB raw for enum modification is safer, but standard change() should work if doctrine/dbal is installed. 
            // The instructions gave: MODIFY `shift` ENUM('matutino', 'vespertino', 'mixto') NULL;
            // Native Laravel schema for enums sometimes fails with change(), so we will define it.
            $table->enum('shift', ['matutino', 'vespertino', 'mixto'])->nullable()->change();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcement_targets', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');

            $table->string('school_level', 50)->nullable(false)->change();
            $table->string('grade', 20)->nullable(false)->change();
            $table->string('group_letter', 20)->nullable(false)->change();
            $table->enum('shift', ['matutino', 'vespertino', 'mixto'])->nullable(false)->change();
        });
    }
};
