<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menerapkan pengaturan dari halaman Settings ke seluruh aplikasi:
 * dibagikan ke view sebagai $appSettings, dan timeout/retries SNMP
 * menimpa config('snmp.*') bila admin sudah menyimpannya.
 */
class ApplyAppSettings
{
    public const DEFAULTS = [
        'app_name'         => 'BMKG Network Monitoring',
        'location'         => 'BMKG Riau',
        'language'         => 'id',
        'theme'            => 'light',
        'polling_interval' => 30,
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $stored = $this->stored();

        $appSettings = [
            // Nama aplikasi tidak boleh kosong (dipakai di judul tab)
            'app_name'         => trim((string) ($stored['app_name'] ?? '')) ?: self::DEFAULTS['app_name'],
            'location'         => (string) ($stored['location'] ?? self::DEFAULTS['location']),
            'language'         => in_array($stored['language'] ?? null, ['id', 'en', 'zh'], true)
                ? $stored['language'] : self::DEFAULTS['language'],
            'theme'            => in_array($stored['theme'] ?? null, ['light', 'dark', 'auto'], true)
                ? $stored['theme'] : self::DEFAULTS['theme'],
            'polling_interval' => $this->clamp($stored['polling_interval'] ?? null, 10, 3600, self::DEFAULTS['polling_interval']),
        ];

        View::share('appSettings', $appSettings);

        // Timeout (detik) → mikrodetik untuk SNMP; hanya jika sudah pernah disimpan
        if (isset($stored['timeout']) && $stored['timeout'] !== '') {
            config(['snmp.timeout' => $this->clamp($stored['timeout'], 1, 60, 5) * 1_000_000]);
        }
        if (isset($stored['retries']) && $stored['retries'] !== '') {
            config(['snmp.retries' => $this->clamp($stored['retries'], 1, 10, 3)]);
        }

        return $next($request);
    }

    /** Membaca semua pengaturan; aman bila tabel belum ada (mis. sebelum migrate). */
    private function stored(): array
    {
        try {
            return Setting::pluck('setting_value', 'setting_key')->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private function clamp(mixed $value, int $min, int $max, int $default): int
    {
        if (! is_numeric($value)) {
            return $default;
        }

        return max($min, min($max, (int) $value));
    }
}
