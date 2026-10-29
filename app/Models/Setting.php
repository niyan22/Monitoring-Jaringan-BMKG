<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'category',
        'description',
    ];

    /**
     * Get a setting by key
     */
    public static function getSetting($key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();

        // Default hanya dipakai jika pengaturan belum pernah disimpan. Nilai kosong
        // yang sengaja disimpan user tetap kosong (bukan kembali ke default).
        return $setting ? ($setting->setting_value ?? '') : $default;
    }

    /**
     * Get all settings by category
     */
    public static function getSettingsByCategory($category)
    {
        return self::where('category', $category)->get();
    }

    /**
     * Update or create a setting
     */
    public static function setSetting($key, $value, $category, $description = null)
    {
        return self::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $value,
                'category' => $category,
                'description' => $description,
            ]
        );
    }
}
