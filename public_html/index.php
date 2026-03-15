<?php

require __DIR__ . '/../vendor/autoload.php';

// Проверка: папка src должна быть рядом с public_html (на хостинге загрузите её)
$srcRoot = __DIR__ . '/../src';
if (!is_dir($srcRoot) || !is_file($srcRoot . '/Domain/User/User.php')) {
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(503);
    echo "Application incomplete. Upload the 'src' folder to the server (same level as public_html).\n";
    echo "Path checked: " . $srcRoot;
    exit;
}

use Slim\Factory\AppFactory;
use App\Domain\User\User;
use App\Domain\Profile\Profile;

$dbConfig = require __DIR__ . '/../config/db.php';
$createPdo = require __DIR__ . '/../config/pdo.php';

// Не подключаться, если остались примеры из db.example.php
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

$routes = require __DIR__ . '/../routes.php';
$routes($app, $pdo);

$app->run();
