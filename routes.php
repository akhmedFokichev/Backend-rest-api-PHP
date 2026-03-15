<?php

use Slim\App;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Controller\UserController;
use App\Http\Controller\ProfileController;

return function (App $app, ?PDO $pdo): void {
    // Публичный маршрут (без авторизации)
    $app->get('/', function ($request, $response) {
        $response->getBody()->write('OK');
        return $response->withHeader('Content-Type', 'text/plain');
    });

    // Проверка подключения к БД
    $app->get('/db-check', function ($request, $response) use ($pdo) {
        if ($pdo === null) {
            $response->getBody()->write(json_encode(['ok' => false, 'error' => 'Database not configured. In config/db.local.php set real credentials (host, dbname, user, pass), not the example your_user/your_database.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(503);
        }
        try {
            $pdo->query('SELECT 1');
            $response->getBody()->write(json_encode(['ok' => true, 'database' => 'connected']));
        } catch (Throwable $e) {
            $response->getBody()->write(json_encode(['ok' => false, 'error' => $e->getMessage()]));
            $response = $response->withStatus(503);
        }
        return $response->withHeader('Content-Type', 'application/json');
    });

    // User: создание и авторизация (публичные). Модель User сама работает с БД (User::setPdo вызывается в index.php).
    if ($pdo !== null) {
        $userController = new UserController();
        $app->post('/user', [$userController, 'create']);
        $app->post('/user/login', [$userController, 'login']);
        $app->delete('/user/{id}', [$userController, 'delete'])->add(AuthMiddleware::class);

        // Profile: у каждого пользователя один профиль (защищённые маршруты)
        $profileController = new ProfileController();
        $app->get('/user/{id}/profile', [$profileController, 'get'])->add(AuthMiddleware::class);
        $app->put('/user/{id}/profile', [$profileController, 'save'])->add(AuthMiddleware::class);
        $app->delete('/user/{id}/profile', [$profileController, 'delete'])->add(AuthMiddleware::class);
    }

    // Защищённые маршруты — требуют заголовок Authorization: Bearer mock-token
    $app->group('/api', function (\Slim\Routing\RouteCollectorProxy $group) use ($pdo) {
        $group->get('/me', function ($request, $response) {
            $response->getBody()->write(json_encode(['authorized' => true, 'message' => 'Mock auth passed']));
            return $response->withHeader('Content-Type', 'application/json');
        });
    })->add(AuthMiddleware::class);
};
