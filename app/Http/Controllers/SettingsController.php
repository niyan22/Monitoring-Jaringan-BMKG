<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index(): View
    {
        $settings = Setting::all();
        $settingsByCategory = $settings->groupBy('category');

        return view('settings.index', [
            'settings' => $settings,
            'settingsByCategory' => $settingsByCategory,
        ]);
    }

    /**
     * Show the form for creating a new setting
     */
    public function create(): View
    {
        return view('settings.create');
    }

    /**
     * Store a newly created setting
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'setting_key' => 'required|string|unique:settings,setting_key',
            'setting_value' => 'required|string',
            'category' => 'required|string|in:general,network,monitoring,notifications,security',
            'description' => 'nullable|string',
        ]);

        Setting::create($validated);

        return redirect()->route('settings.index')->with('success', 'Pengaturan berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified setting
     */
    public function edit(Setting $setting): View
    {
        return view('settings.edit', ['setting' => $setting]);
    }

    /**
     * Update the specified setting
     */
    public function update(Request $request, Setting $setting): RedirectResponse
    {
        $validated = $request->validate([
            'setting_key' => 'required|string|unique:settings,setting_key,' . $setting->id,
            'setting_value' => 'required|string',
            'category' => 'required|string|in:general,network,monitoring,notifications,security',
            'description' => 'nullable|string',
        ]);

        $setting->update($validated);

        return redirect()->route('settings.index')->with('success', 'Pengaturan berhasil diperbarui');
    }

    /**
     * Delete the specified setting
     */
    public function destroy(Setting $setting): RedirectResponse
    {
        $setting->delete();

        return redirect()->route('settings.index')->with('success', 'Pengaturan berhasil dihapus');
    }

    /**
     * Save all settings from the form
     */
    public function saveAll(Request $request): RedirectResponse
    {
        $request->validate([
            'app_name'         => 'sometimes|nullable|string|max:100',
            'location'         => 'sometimes|nullable|string|max:100',
            'timezone'         => 'sometimes|nullable|string|in:Asia/Jakarta,Asia/Bangkok,Asia/Singapore,Asia/Tokyo',
            'language'         => 'sometimes|nullable|string|in:id,en,zh',
            'theme'            => 'sometimes|nullable|string|in:light,dark,auto',
            'polling_interval' => 'sometimes|nullable|integer|min:10|max:3600',
            'timeout'          => 'sometimes|nullable|integer|min:1|max:60',
            'retries'          => 'sometimes|nullable|integer|min:1|max:10',
            'data_retention'   => 'sometimes|nullable|in:7,30,90',
            'alert_email'      => 'sometimes|nullable|email|max:255',
            'telegram_token'   => 'sometimes|nullable|string|max:255',
            'telegram_chat_id' => 'sometimes|nullable|string|max:100',
        ]);

        // Save General Settings
        if ($request->has('app_name')) {
            Setting::setSetting('app_name', $request->app_name, 'general', 'Application Name');
        }
        if ($request->has('location')) {
            Setting::setSetting('location', $request->location, 'general', 'Location');
        }
        if ($request->has('timezone')) {
            Setting::setSetting('timezone', $request->timezone, 'general', 'Timezone');
        }
        if ($request->has('language')) {
            Setting::setSetting('language', $request->language, 'general', 'Language');
        }
        if ($request->has('theme')) {
            Setting::setSetting('theme', $request->theme, 'general', 'Theme');
        }

        // Save Network Settings
        if ($request->has('ping_enabled')) {
            Setting::setSetting('ping_enabled', 'true', 'network', 'Ping (ICMP) Enabled');
        } else {
            Setting::setSetting('ping_enabled', 'false', 'network', 'Ping (ICMP) Enabled');
        }
        if ($request->has('snmp_enabled')) {
            Setting::setSetting('snmp_enabled', 'true', 'network', 'SNMP Enabled');
        } else {
            Setting::setSetting('snmp_enabled', 'false', 'network', 'SNMP Enabled');
        }
        if ($request->has('http_enabled')) {
            Setting::setSetting('http_enabled', 'true', 'network', 'HTTP/HTTPS Enabled');
        } else {
            Setting::setSetting('http_enabled', 'false', 'network', 'HTTP/HTTPS Enabled');
        }
        if ($request->has('polling_interval')) {
            Setting::setSetting('polling_interval', $request->polling_interval, 'network', 'Polling Interval');
        }
        if ($request->has('timeout')) {
            Setting::setSetting('timeout', $request->timeout, 'network', 'Timeout');
        }
        if ($request->has('retries')) {
            Setting::setSetting('retries', $request->retries, 'network', 'Retries');
        }

        // Save Monitoring Settings
        if ($request->has('data_retention')) {
            Setting::setSetting('data_retention', $request->data_retention, 'monitoring', 'Data Retention Days');
        }
        if ($request->has('realtime_graphs')) {
            Setting::setSetting('realtime_graphs', 'true', 'monitoring', 'Real-time Graphs');
        } else {
            Setting::setSetting('realtime_graphs', 'false', 'monitoring', 'Real-time Graphs');
        }
        if ($request->has('show_trends')) {
            Setting::setSetting('show_trends', 'true', 'monitoring', 'Show Trends');
        } else {
            Setting::setSetting('show_trends', 'false', 'monitoring', 'Show Trends');
        }

        // Save Notification Settings
        if ($request->has('email_notifications')) {
            Setting::setSetting('email_notifications', 'true', 'notifications', 'Email Notifications');
        } else {
            Setting::setSetting('email_notifications', 'false', 'notifications', 'Email Notifications');
        }
        if ($request->has('sms_alerts')) {
            Setting::setSetting('sms_alerts', 'true', 'notifications', 'SMS Alerts');
        } else {
            Setting::setSetting('sms_alerts', 'false', 'notifications', 'SMS Alerts');
        }
        if ($request->has('telegram_alerts')) {
            Setting::setSetting('telegram_alerts', 'true', 'notifications', 'Telegram Alerts');
        } else {
            Setting::setSetting('telegram_alerts', 'false', 'notifications', 'Telegram Alerts');
        }
        if ($request->has('alert_email')) {
            Setting::setSetting('alert_email', $request->alert_email, 'notifications', 'Alert Email');
        }
        if ($request->has('telegram_token')) {
            Setting::setSetting('telegram_token', $request->telegram_token, 'notifications', 'Telegram Bot Token');
        }
        if ($request->has('telegram_chat_id')) {
            Setting::setSetting('telegram_chat_id', $request->telegram_chat_id, 'notifications', 'Telegram Chat ID');
        }

        // Save Security Settings
        if ($request->has('two_factor_auth')) {
            Setting::setSetting('two_factor_auth', 'true', 'security', 'Two Factor Authentication');
        } else {
            Setting::setSetting('two_factor_auth', 'false', 'security', 'Two Factor Authentication');
        }

        // Tema yang baru disimpan menggantikan pilihan tombol dark mode di browser
        return redirect()->route('settings.index')
            ->with('success', 'Semua pengaturan berhasil disimpan')
            ->with('theme_reset', $request->has('theme'));
    }
}
