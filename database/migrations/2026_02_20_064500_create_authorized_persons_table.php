<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authorized_persons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('full_name', 150);
            $table->string('relationship', 50);
            $table->string('phone', 20)->nullable();
            $table->string('photo_path')->nullable();
            $table->string('photo_hash')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authorized_persons');
    }
};
