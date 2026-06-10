<?php

/**
 * index.php — HTTP-вход админ-панели на хостинге.
 *
 * Назначение: задаёт BASE_PATH и запускает admin/bootstrap.php для URL /admin/*.
 */

define('BASE_PATH', dirname(__DIR__, 2) . '/admin');
define('APP_BASE_PATH', '/admin');

require BASE_PATH . '/bootstrap.php';
