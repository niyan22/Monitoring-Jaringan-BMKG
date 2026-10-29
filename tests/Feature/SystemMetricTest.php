<?php

namespace Tests\Feature;

use App\Models\SystemMetric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemMetricTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'cpu_load'            => 45.5,
            'memory_used'         => 4,
            'memory_total'        => 16,
            'disk_used'           => 100,
            'disk_total'          => 500,
            'processor_name'      => 'Intel Test CPU',
            'processor_cores'     => 4,
            'processor_frequency' => 3.2,
        ], $overrides);
    }

    private function metric(array $overrides = []): SystemMetric
    {
        return SystemMetric::create($this->payload($overrides) + ['recorded_at' => now()]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/system')->assertRedirect('/login');
        $this->get('/system/latest-json')->assertRedirect('/login');
        $this->post('/system', $this->payload())->assertRedirect('/login');
        $this->assertDatabaseCount('system_metrics', 0);
    }

    public function test_index_page_is_displayed(): void
    {
        $this->metric();

        $this->actingAs(User::factory()->create())
            ->get('/system')
            ->assertOk();
    }

    public function test_metric_can_be_stored(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/system', $this->payload())
            ->assertRedirect(route('system'));

        $this->assertDatabaseHas('system_metrics', ['processor_name' => 'Intel Test CPU', 'cpu_load' => 45.5]);
    }

    public function test_store_rejects_invalid_input(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/system', $this->payload(['cpu_load' => 150, 'processor_cores' => 0]))
            ->assertSessionHasErrors(['cpu_load', 'processor_cores']);

        $this->assertDatabaseCount('system_metrics', 0);
    }

    public function test_store_requires_all_fields(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/system', [])
            ->assertSessionHasErrors([
                'cpu_load', 'memory_used', 'memory_total', 'disk_used',
                'disk_total', 'processor_name', 'processor_cores', 'processor_frequency',
            ]);
    }

    public function test_metric_can_be_updated(): void
    {
        $metric = $this->metric();

        $this->actingAs(User::factory()->create())
            ->patch("/system/{$metric->id}", $this->payload(['cpu_load' => 10]))
            ->assertRedirect(route('system'));

        $this->assertSame(10.0, $metric->fresh()->cpu_load);
    }

    public function test_metric_can_be_deleted(): void
    {
        $metric = $this->metric();

        $this->actingAs(User::factory()->create())
            ->delete("/system/{$metric->id}")
            ->assertRedirect(route('system'));

        $this->assertDatabaseMissing('system_metrics', ['id' => $metric->id]);
    }

    public function test_latest_json_without_data(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson('/system/latest-json')
            ->assertOk()
            ->assertJson(['success' => false]);
    }

    public function test_latest_json_returns_latest_metric_with_percentages(): void
    {
        $this->metric(['cpu_load' => 1, 'recorded_at' => now()->subHour()]);
        $this->metric(['cpu_load' => 80, 'memory_used' => 8, 'memory_total' => 16, 'disk_used' => 250, 'disk_total' => 500]);

        $this->actingAs(User::factory()->create())
            ->getJson('/system/latest-json')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.cpu_load', 80)
            ->assertJsonPath('data.memory_pct', 50)
            ->assertJsonPath('data.disk_pct', 50);
    }
}
