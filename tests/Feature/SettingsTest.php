<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/settings')->assertRedirect('/login');
        $this->post('/settings/save-all', ['app_name' => 'X'])->assertRedirect('/login');
        $this->assertDatabaseCount('settings', 0);
    }

    public function test_settings_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/settings')
            ->assertOk();
    }

    public function test_save_all_stores_text_values_and_checkbox_flags(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/settings/save-all', [
                'app_name'     => 'BMKG Test',
                'ping_enabled' => 'on',
            ])
            ->assertRedirect(route('settings.index'));

        $this->assertSame('BMKG Test', Setting::getSetting('app_name'));
        $this->assertSame('true', Setting::getSetting('ping_enabled'));
        // Checkbox yang tidak dikirim dianggap nonaktif
        $this->assertSame('false', Setting::getSetting('snmp_enabled'));
    }

    public function test_save_all_updates_existing_value_without_duplicating(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/settings/save-all', ['app_name' => 'Lama']);
        $this->actingAs($user)->post('/settings/save-all', ['app_name' => 'Baru']);

        $this->assertSame('Baru', Setting::getSetting('app_name'));
        $this->assertSame(1, Setting::where('setting_key', 'app_name')->count());
    }
}
