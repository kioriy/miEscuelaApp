<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Súper Administrador Global (Agencia)
        User::updateOrCreate(
            ['email' => 'ikioriy@gmail.com'],
            [
                'name' => 'Súper Admin',
                'school_id' => null, // NULL define alcance global según el schema
                'password' => null, // El ingreso es vía Google OAuth
                'role' => 'super_admin',
            ]
        );
    }
}
