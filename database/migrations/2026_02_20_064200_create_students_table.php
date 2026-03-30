<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('enrollment_code', 50)->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('tutor_email')->nullable();
            $table->string('secondary_tutor_email')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('photo_hash')->nullable();
            $table->string('school_level', 50)->nullable();
            $table->string('grade', 20)->nullable();
            $table->string('group_letter', 20)->nullable();
            $table->enum('shift', ['matutino', 'vespertino', 'mixto'])->default('matutino');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
