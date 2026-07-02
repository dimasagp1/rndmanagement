<?php

if (! function_exists('setting')) {
    function setting($key, $default = null) {
        try {
            $setting = \App\Models\Setting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
