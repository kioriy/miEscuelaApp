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
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('school_level', 50);
            $table->string('grade', 20);
            $table->string('group_letter', 20);
            $table->enum('shift', ['matutino', 'vespertino', 'mixto'])->default('matutino');
            $table->timestamps();

            $table->unique(['school_id', 'school_level', 'grade', 'group_letter', 'shift'], 'unique_group_per_school');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
