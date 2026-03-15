<?php

/**
 * Включите на хостинге для диагностики 500: замените false на true,
 * обновите страницу — в ответе увидите текст ошибки. Потом верните false.
 */
define('APP_DEBUG', true);

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

if (PHP_VERSION_ID < 80100) {
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(503);
    echo 'Требуется PHP 8.1 или выше. Сейчас: ' . PHP_VERSION . '. Смените версию PHP в панели хостинга.';
    exit;
}

$autoload = __DIR__ . '/vendor/autoload.php';
if (!is_file($autoload)) {
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(503);
    echo "Ошибка: папка vendor не найдена. Выполните composer install и загрузите папку vendor на хостинг.";
    exit;
}

require $autoload;

use Slim\Factory\AppFactory;
use App\Domain\User\User;
use App\Domain\Profile\Profile;

try {
    $dbConfig = require __DIR__ . '/config/db.php';
    $createPdo = require __DIR__ . '/config/pdo.php';
    $isExample = ($dbConfig['user'] === 'your_user' || ($dbConfig['dbname'] ?? '') === 'your_database');
    $pdo = (!$isExample && $dbConfig['dbname'] !== '' && $dbConfig['user'] !== '')
        ? $createPdo($dbConfig)
        : null;

    if ($pdo !== null) {
        User::setPdo($pdo);
        Profile::setPdo($pdo);
    }

    $app = AppFactory::create();
    $app->addRoutingMiddleware();
    $app->addBodyParsingMiddleware();
    $app->addErrorMiddleware(true, true, true);

    $routes = require __DIR__ . '/routes.php';
    $routes($app, $pdo);

    $app->run();
} catch (Throwable $e) {
    if (APP_DEBUG) {
        header('Content-Type: text/plain; charset=utf-8');
        http_response_code(500);
        echo $e->getMessage() . "\n\n" . $e->getTraceAsString();
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        http_response_code(500);
        echo 'Ошибка сервера. Включите APP_DEBUG в index.php для диагностики.';
    }
}
