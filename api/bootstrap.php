<?php

/**
 * bootstrap.php — точка запуска Slim API.
 *
 * Назначение: подключает autoload, БД (Medoo), регистрирует маршруты и запускает приложение.
 * Вызывается из: public_html/api/index.php.
 */

declare(strict_types=1);

define('API_DEBUG', true);

if (API_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

$projectRoot = dirname(__DIR__);

require $projectRoot . '/vendor/autoload.php';

$srcRoot = $projectRoot . '/api/src';
if (!is_dir($srcRoot) || !is_file($srcRoot . '/Domain/User/User.php')) {
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(503);
    echo "API incomplete: api/src not found.\n";
    exit;
}

use Slim\Factory\AppFactory;
use App\Domain\Auth\UserToken;
use App\Domain\User\User;
use App\Domain\Profile\Profile;

try {
    $dbConfig = require $projectRoot . '/config/db.php';
    $createDb = require $projectRoot . '/config/medoo.php';

    $isExample = ($dbConfig['user'] === 'your_user' || ($dbConfig['dbname'] ?? '') === 'your_database');
    $db = (!$isExample && $dbConfig['dbname'] !== '' && $dbConfig['user'] !== '')
        ? $createDb($dbConfig)
        : null;

    if ($db !== null) {
        UserToken::setDb($db);
        User::setDb($db);
        Profile::setDb($db);
    }

    $app = AppFactory::create();
    $app->addRoutingMiddleware();
    $app->addBodyParsingMiddleware();
    $app->addErrorMiddleware(API_DEBUG, true, true);

    $routes = require $projectRoot . '/api/routes.php';
    $routes($app, $db);

    $app->run();
} catch (Throwable $e) {
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(500);
    if (API_DEBUG) {
        echo $e->getMessage() . "\n\n" . $e->getTraceAsString();
    } else {
        echo 'API error.';
    }
}
