<?php

declare(strict_types=1);

namespace App\Core;

final class Url
{
    public static function to(string $path = ''): string
    {
        $base = defined('APP_BASE_PATH') ? rtrim((string) APP_BASE_PATH, '/') : '';

        if ($path === '') {
            return $base !== '' ? $base : '/';
        }

        return $base . '/' . ltrim($path, '/');
    }

    public static function asset(string $path): string
    {
        return self::to('assets/' . ltrim($path, '/'));
    }
}
