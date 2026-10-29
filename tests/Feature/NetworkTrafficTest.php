<?php

namespace Tests\Feature;

use App\Models\NetworkTraffic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NetworkTrafficTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'interface_name'          => 'ether1',
            'download_speed'          => 4.5,
            'upload_speed'            => 1.2,
            'packets_sent'            => 100,
            'packets_received'        => 200,
            'bytes_sent'              => 1000,
            'bytes_received'          => 2000,
            'active_connections'      => 10,
            'established_connections' => 5,
        ], $overrides);
    }

    private function traffic(array $overrides = []): NetworkTraffic
    {
        return NetworkTraffic::create($this->payload($overrides) + ['recorded_at' => now()]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/network')->assertRedirect('/login');
        $this->get('/network/latest-json')->assertRedirect('/login');
        $this->post('/network', $this->payload())->assertRedirect('/login');
        $this->assertDatabaseCount('network_traffic', 0);
    }

    public function test_index_page_is_displayed(): void
    {
        $this->traffic();

        $this->actingAs(User::factory()->create())
            ->get('/network')
            ->assertOk();
    }

    public function test_traffic_can_be_stored(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/network', $this->payload())
            ->assertRedirect(route('network'));

        $this->assertDatabaseHas('network_traffic', ['interface_name' => 'ether1', 'download_speed' => 4.5]);
    }

    public function test_store_rejects_negative_and_non_numeric_values(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/network', $this->payload(['download_speed' => -1, 'packets_sent' => 'abc']))
            ->assertSessionHasErrors(['download_speed', 'packets_sent']);

        $this->assertDatabaseCount('network_traffic', 0);
    }

    public function test_traffic_can_be_updated(): void
    {
        $traffic = $this->traffic();

        $this->actingAs(User::factory()->create())
            ->patch("/network/{$traffic->id}", $this->payload(['download_speed' => 9.9]))
            ->assertRedirect(route('network'));

        $this->assertSame(9.9, $traffic->fresh()->download_speed);
    }

    public function test_traffic_can_be_deleted(): void
    {
        $traffic = $this->traffic();

        $this->actingAs(User::factory()->create())
            ->delete("/network/{$traffic->id}")
            ->assertRedirect(route('network'));

        $this->assertDatabaseMissing('network_traffic', ['id' => $traffic->id]);
    }

    public function test_latest_json_returns_latest_record(): void
    {
        $this->traffic(['download_speed' => 1, 'recorded_at' => now()->subHour()]);
        $this->traffic(['download_speed' => 7.5]);

        $this->actingAs(User::factory()->create())
            ->getJson('/network/latest-json')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.download_speed', 7.5);
    }
}
