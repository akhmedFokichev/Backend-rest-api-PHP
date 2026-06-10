<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

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

$router = new Router();
$auth = new AuthController();
$pages = new PageController();
$proxy = new ProxyController();

$requireAuth = static fn () => Auth::requireAuth();
$requireGuest = static fn () => Auth::requireGuest();
$canUsers = static fn () => Auth::requirePermission('users.view');

$router->get('/', static fn () => header('Location: /admin'), []);
$router->get('/login', [$auth, 'showLogin'], [$requireGuest]);
$router->post('/login', [$auth, 'login'], [$requireGuest]);
$router->get('/logout', [$auth, 'logout'], [$requireAuth]);

$router->get('/admin', [$pages, 'dashboard'], [$requireAuth]);
$router->get('/admin/users', [$pages, 'usersIndex'], [$canUsers]);

// Proxy: any /api/proxy/* → Slim API (same origin for JS)
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if (str_starts_with($uri, '/api/proxy/')) {
    $proxy->handle();
    exit;
}

$router->dispatch($method, $uri);
