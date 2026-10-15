<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public const CACHE_KEY = 'settings.all';

    protected $fillable = ['key', 'value'];

    protected static function booted(): void
    {
        // Helper setting() membaca dari cache, jadi bersihkan setiap kali ada perubahan
        $flush = fn () => Cache::memo()->forget(self::CACHE_KEY);

        static::saved($flush);
        static::deleted($flush);
    }
}
