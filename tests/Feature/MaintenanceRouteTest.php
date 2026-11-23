<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_maintenance_route_returns_404_when_disabled_by_default(): void
    {
        config(['maintenance.enabled' => false]);

        $this->post('/_maintenance/cache-clear')->assertNotFound();
    }

    public function test_maintenance_route_enforces_ip_expiry_token_and_command_allowlist(): void
    {
        $validToken = str_repeat('a', 40);

        config([
            'maintenance.enabled' => true,
            'maintenance.token' => $validToken,
            'maintenance.expires_at' => now()->addHour()->toIso8601String(),
            'maintenance.allowed_ips' => ['127.0.0.1'],
        ]);

        // Bad token -> 403
        $this->withHeaders(['X-Maintenance-Token' => 'wrong'])
            ->post('/_maintenance/cache-clear')
            ->assertForbidden();

        // Non-allowlisted command -> 422
        $this->withHeaders(['X-Maintenance-Token' => $validToken])
            ->post('/_maintenance/migrate-fresh')
            ->assertUnprocessable();

        // Valid token + allowlisted IP + allowlisted command -> 200 + audit log recorded
        $this->withHeaders(['X-Maintenance-Token' => $validToken])
            ->post('/_maintenance/cache-clear')
            ->assertOk()
            ->assertJson(['action' => 'cache-clear', 'exit_code' => 0]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'maintenance.executed']);
    }
}
