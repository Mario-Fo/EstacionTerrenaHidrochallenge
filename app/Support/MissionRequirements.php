<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class MissionRequirements
{
    private const STORAGE_FILE = 'app/mission_requirements.json';

    /**
     * @return array{altitude_threshold: float, air_time_threshold: float, fall_speed_threshold: float}
     */
    public static function all(): array
    {
        $defaults = self::defaults();
        $path = storage_path(self::STORAGE_FILE);

        if (! File::exists($path)) {
            return $defaults;
        }

        $decoded = json_decode((string) File::get($path), true);

        if (! is_array($decoded)) {
            return $defaults;
        }

        return [
            'altitude_threshold' => self::toFloat($decoded['altitude_threshold'] ?? $defaults['altitude_threshold']),
            'air_time_threshold' => self::toFloat($decoded['air_time_threshold'] ?? $defaults['air_time_threshold']),
            'fall_speed_threshold' => self::toFloat($decoded['fall_speed_threshold'] ?? $defaults['fall_speed_threshold']),
        ];
    }

    /**
     * @param array{altitude_threshold: float|int|string, air_time_threshold: float|int|string, fall_speed_threshold: float|int|string} $values
     */
    public static function save(array $values): void
    {
        $normalized = [
            'altitude_threshold' => self::toFloat($values['altitude_threshold'] ?? self::defaults()['altitude_threshold']),
            'air_time_threshold' => self::toFloat($values['air_time_threshold'] ?? self::defaults()['air_time_threshold']),
            'fall_speed_threshold' => self::toFloat($values['fall_speed_threshold'] ?? self::defaults()['fall_speed_threshold']),
        ];

        $path = storage_path(self::STORAGE_FILE);
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @return array{altitude_threshold: float, air_time_threshold: float, fall_speed_threshold: float}
     */
    public static function defaults(): array
    {
        return [
            'altitude_threshold' => 100.0,
            'air_time_threshold' => 45.0,
            'fall_speed_threshold' => 8.0,
        ];
    }

    private static function toFloat(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }
}
