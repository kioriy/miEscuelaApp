<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\School;
use App\Models\Kiosk;

class KioskSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_activate_kiosk_successfully()
    {
        $school = School::create(['name' => 'Demo School', 'slug' => 'demo', 'allowed_kiosks' => 1]);
        $kiosk = Kiosk::create([
            'school_id' => $school->id,
            'activation_code' => 'X7B-902',
            'is_active' => false
        ]);

        $response = $this->postJson('/api/setup/kiosk/activate', [
            'activation_code' => 'X7B-902'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'token']);

        $this->assertDatabaseHas('kiosks', [
            'id' => $kiosk->id,
            'is_active' => true,
        ]);
    }

    public function test_activate_kiosk_fails_limit_exceeded()
    {
        $school = School::create(['name' => 'Demo limit', 'slug' => 'demo-limit', 'allowed_kiosks' => 1]);

        // Active kiosk
        Kiosk::create([
            'school_id' => $school->id,
            'activation_code' => 'K1-ACTIVE',
            'is_active' => true
        ]);

        // Inactive kiosk waiting activation
        Kiosk::create([
            'school_id' => $school->id,
            'activation_code' => 'K2-NEW',
            'is_active' => false
        ]);

        $response = $this->postJson('/api/setup/kiosk/activate', [
            'activation_code' => 'K2-NEW'
        ]);

        $response->assertStatus(403)
            ->assertJson(['success' => false]);
    }
}
