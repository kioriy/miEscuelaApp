<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitorSyncTest extends TestCase
{
    public function test_pull_data_requires_authentication()
    {
        $response = $this->getJson('/api/sync/monitor/pull');

        $response->assertStatus(401);
    }

    public function test_push_data_requires_authentication()
    {
        $response = $this->postJson('/api/sync/monitor/push', []);

        $response->assertStatus(401);
    }
}
