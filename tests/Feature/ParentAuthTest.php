<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentAuthTest extends TestCase
{
    public function test_login_rejects_without_token()
    {
        $response = $this->postJson('/api/auth/parent/google', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    public function test_login_handles_invalid_google_token()
    {
        $response = $this->postJson('/api/auth/parent/google', [
            'token' => 'fake_invalid_token'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }
}
