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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 50)->unique();
            $table->string('logo_path')->nullable();
            $table->string('timezone', 50)->default('America/Mexico_City');
            $table->integer('allowed_kiosks')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('kiosks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('activation_code', 20)->unique();
            $table->string('name', 50)->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('device_token')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kiosks');
        Schema::dropIfExists('schools');
    }
};
