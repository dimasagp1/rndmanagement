<?php

if (! function_exists('setting')) {
    /**
     * Ambil nilai pengaturan sistem. Semua pengaturan dibaca sekali lalu di-cache
     * (dan di-memo per request); cache dibersihkan otomatis saat Setting disimpan.
     */
    function setting($key, $default = null) {
        try {
            $settings = \Illuminate\Support\Facades\Cache::memo()->rememberForever(
                \App\Models\Setting::CACHE_KEY,
                fn () => \App\Models\Setting::pluck('value', 'key')->all()
            );

            return array_key_exists($key, $settings) ? $settings[$key] : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
