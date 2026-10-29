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

    public function test_empty_value_stays_empty_instead_of_falling_back_to_default(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/settings/save-all', ['location' => 'Pekanbaru']);
        $this->actingAs($user)->post('/settings/save-all', ['location' => '']);

        $this->assertSame('', Setting::getSetting('location', 'BMKG Riau'));
        // Default hanya dipakai jika pengaturan belum pernah disimpan
        $this->assertSame('fallback', Setting::getSetting('belum_ada', 'fallback'));
    }

    public function test_save_all_rejects_invalid_values(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/settings/save-all', [
                'polling_interval' => 1,
                'timeout'          => 999,
                'theme'            => 'neon',
                'alert_email'      => 'bukan-email',
            ])
            ->assertSessionHasErrors(['polling_interval', 'timeout', 'theme', 'alert_email']);

        $this->assertDatabaseCount('settings', 0);
    }

    public function test_saved_app_name_is_used_in_page_title(): void
    {
        $user = User::factory()->create();
        Setting::setSetting('app_name', 'Monitoring Uji', 'general');

        $this->actingAs($user)->get('/settings')
            ->assertOk()
            ->assertSee('<title>Settings — Monitoring Uji</title>', false);
    }

    public function test_saved_polling_interval_is_applied_to_pages(): void
    {
        Setting::setSetting('polling_interval', '45', 'network');

        $this->actingAs(User::factory()->create())->get('/system')
            ->assertOk()
            ->assertSee('const POLL_INTERVAL   = 45;', false)
            ->assertSee('Auto (45s)', false);
    }

    public function test_polling_interval_is_clamped_to_safe_range(): void
    {
        Setting::setSetting('polling_interval', '1', 'network');

        $this->actingAs(User::factory()->create())->get('/system')
            ->assertSee('const POLL_INTERVAL   = 10;', false);
    }

    public function test_saved_snmp_timeout_and_retries_override_config(): void
    {
        Setting::setSetting('timeout', '7', 'network');
        Setting::setSetting('retries', '2', 'network');

        $this->actingAs(User::factory()->create())->get('/settings')->assertOk();

        $this->assertSame(7_000_000, config('snmp.timeout'));
        $this->assertSame(2, config('snmp.retries'));
    }

    public function test_saving_theme_resets_browser_dark_mode_override(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/settings/save-all', ['theme' => 'dark'])
            ->assertSessionHas('theme_reset', true);
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
