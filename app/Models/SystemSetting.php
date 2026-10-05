<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'messages_enabled',
        'notifications_enabled',
        'updated_by',
    ];

    protected $casts = [
        'messages_enabled' => 'boolean',
        'notifications_enabled' => 'boolean',
    ];

    public static function current(): self
    {
        return Cache::rememberForever('system_settings', function () {
            return static::first() ?? static::create([
                'messages_enabled' => true,
                'notifications_enabled' => true,
            ]);
        });
    }

    public static function forgetCache(): void
    {
        Cache::forget('system_settings');
    }
}
