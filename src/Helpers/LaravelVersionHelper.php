<?php

namespace Equidna\Caronte\Helpers;

class LaravelVersionHelper
{
    /**
     * Get the major Laravel version as an integer.
     */
    public static function getMajorVersion(): int
    {
        $version = app()->version();
        if (preg_match('/^(\d+)/', $version, $matches)) {
            return (int) $matches[1];
        }
        return 0;
    }

    /**
     * Check if running Laravel 12 or higher.
     */
    public static function isLaravel12OrHigher(): bool
    {
        return self::getMajorVersion() >= 12;
    }
}
