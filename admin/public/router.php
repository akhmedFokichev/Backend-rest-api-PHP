<?php

/**
 * router.php — роутер встроенного PHP-сервера для админки.
 *
 * Назначение: отдаёт статику как есть, остальное направляет в index.php.
 * Запуск: php -S localhost:8080 router.php (из admin/public).
 */

declare(strict_types=1);
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

if ($uri !== '/' && is_file(__DIR__ . $uri)) {
    return false;
}

require __DIR__ . '/index.php';
