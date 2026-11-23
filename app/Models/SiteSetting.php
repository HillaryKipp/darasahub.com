<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $settings = Cache::remember('site_settings.all', 300, function (): array {
            return static::query()->get()->keyBy('key')->map(function (self $setting) {
                return match ($setting->type) {
                    'integer' => (int) $setting->value,
                    'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                    'json' => json_decode((string) $setting->value, true),
                    default => $setting->value,
                };
            })->all();
        });

        return $settings[$key] ?? $default;
    }

    public static function setValue(string $key, mixed $value, string $type = 'string', string $group = 'general', ?string $description = null): self
    {
        $stored = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };

        $setting = static::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $stored,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );

        Cache::forget('site_settings.all');

        return $setting;
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site_settings.all'));
        static::deleted(fn () => Cache::forget('site_settings.all'));
    }
}
