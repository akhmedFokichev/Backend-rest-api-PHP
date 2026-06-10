<?php

/**
 * index.php — вход админки для локального PHP-сервера.
 *
 * Назначение: задаёт APP_BASE_PATH=/admin и подключает admin/bootstrap.php.
 */

define('APP_BASE_PATH', '/admin');
require dirname(__DIR__) . '/bootstrap.php';
