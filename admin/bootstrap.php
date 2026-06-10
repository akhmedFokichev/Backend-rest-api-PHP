<?php

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

$proxyPrefix = Url::to('api/proxy/');
if (str_starts_with($uri, $proxyPrefix)) {
    $proxy->handle();
    exit;
}

$router->dispatch($method, $uri);
