<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('school_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('director'); // default role in the school context
            $table->timestamps();

            $table->unique(['user_id', 'school_id']);
        });

        // Data migration: Copy existing school_id from users to school_user
        $users = DB::table('users')->whereNotNull('school_id')->get();
        foreach ($users as $user) {
            DB::table('school_user')->insert([
                'user_id' => $user->id,
                'school_id' => $user->school_id,
                'role' => $user->role ?? 'director',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_user');
    }
};
