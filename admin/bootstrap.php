<?php

/**
 * bootstrap.php — ядро админ-панели.
 *
 * Назначение: автозагрузка, сессия, маршруты (/admin, /admin/login, /admin/users), proxy к API.
 * Вызывается из: public_html/admin/index.php или admin/public/index.php.
 */

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

if (!defined('APP_BASE_PATH')) {
    define('APP_BASE_PATH', '/admin');
}

session_start();

date_default_timezone_set(
    (require BASE_PATH . '/app/Config/app.php')['timezone'] ?? 'UTC'
);

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = BASE_PATH . '/app/' . $relative . '.php';

    if (is_file($file)) {
        require $file;
    }
});

use App\Controllers\AuthController;
use App\Controllers\PageController;
use App\Controllers\ProxyController;
use App\Core\Auth;
use App\Core\Router;
use App\Core\Url;

$router = new Router();
$auth = new AuthController();
$pages = new PageController();
$proxy = new ProxyController();

$requireAuth = static fn () => Auth::requireAuth();
$requireGuest = static fn () => Auth::requireGuest();
$canUsers = static fn () => Auth::requirePermission('users.view');

$router->get(Url::to(), [$pages, 'dashboard'], [$requireAuth]);
$router->get(Url::to('login'), [$auth, 'showLogin'], [$requireGuest]);
$router->post(Url::to('login'), [$auth, 'login'], [$requireGuest]);
$router->get(Url::to('logout'), [$auth, 'logout'], [$requireAuth]);
$router->get(Url::to('users'), [$pages, 'usersIndex'], [$canUsers]);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$assetsPrefix = Url::to('assets/');
if (str_starts_with($uri, $assetsPrefix)) {
    $relative = ltrim(substr($uri, strlen($assetsPrefix)), '/');
    if ($relative === '' || str_contains($relative, '..')) {
        http_response_code(404);
        exit;
    }

    $file = BASE_PATH . '/public/assets/' . $relative;
    if (!is_file($file)) {
        http_response_code(404);
        exit;
    }

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $types = [
        'css' => 'text/css; charset=utf-8',
        'js' => 'application/javascript; charset=utf-8',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];

    header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
    header('Cache-Control: public, max-age=86400');
    readfile($file);
    exit;
}

$proxyPrefix = Url::to('api/proxy/');
if (str_starts_with($uri, $proxyPrefix)) {
    $proxy->handle();
    exit;
}

$router->dispatch($method, $uri);
