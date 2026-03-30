<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->dateTime('scanned_at');
            $table->enum('type', ['in', 'out']);
            $table->unsignedBigInteger('kiosk_id')->nullable();
            $table->unsignedBigInteger('recorded_by_user_id')->nullable();
            $table->unsignedBigInteger('authorized_person_id')->nullable();
            $table->string('sync_status', 20)->default('synced');
            $table->timestamp('created_at')->nullable();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('kiosk_id')->references('id')->on('kiosks')->nullOnDelete();
            $table->foreign('recorded_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
