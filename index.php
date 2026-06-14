<?php

/**
 * index.php — фронт-контроллер, если document root = корень проекта.
 *
 * Назначение: маршрутизация / → лендинг, /api/* → Slim, /admin/* → админка.
 */

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if (str_starts_with($uri, '/api/')) {
    require __DIR__ . '/public_html/api/index.php';
    exit;
}

if (str_starts_with($uri, '/admin')) {
    require __DIR__ . '/public_html/admin/index.php';
    exit;
}

require __DIR__ . '/public_html/index.php';
