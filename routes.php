<?php

use Slim\App;
use Medoo\Medoo;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Controller\UserController;
use App\Http\Controller\ProfileController;

return function (App $app, ?Medoo $db): void {
    // Публичный маршрут (без авторизации)
    $app->get('/', function ($request, $response) {
        $response->getBody()->write('OK');
        return $response->withHeader('Content-Type', 'text/plain');
    });

    // Все API-эндпоинты идут под /api/v1/*
    $app->group('/api/v1', function (\Slim\Routing\RouteCollectorProxy $group) use ($db) {
        // Проверка подключения к БД
        $group->get('/db-check', function ($request, $response) use ($db) {
            if ($db === null) {
                $response->getBody()->write(json_encode(['ok' => false, 'error' => 'Database not configured. In config/db.local.php set real credentials (host, dbname, user, pass), not the example your_user/your_database.']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(503);
            }
            try {
                $db->query('SELECT 1');
                $response->getBody()->write(json_encode(['ok' => true, 'database' => 'connected']));
            } catch (Throwable $e) {
                $response->getBody()->write(json_encode(['ok' => false, 'error' => $e->getMessage()]));
                $response = $response->withStatus(503);
            }
            return $response->withHeader('Content-Type', 'application/json');
        });

        // User: публичные маршруты (без auth)
        if ($db !== null) {
            $userController = new UserController();
            $profileController = new ProfileController();

            $group->post('/user/registration', [$userController, 'create']);
            $group->post('/user/login', [$userController, 'login']);
            $group->post('/user/logout', [$userController, 'logout'])->add(AuthMiddleware::class);

            // Защищённые маршруты — требуют Authorization: Bearer mock-token
            $group->delete('/user/{id}', [$userController, 'delete'])->add(AuthMiddleware::class);
            $group->get('/user/{id}/profile', [$profileController, 'get'])->add(AuthMiddleware::class);
            $group->put('/user/{id}/profile', [$profileController, 'save'])->add(AuthMiddleware::class);
            $group->delete('/user/{id}/profile', [$profileController, 'delete'])->add(AuthMiddleware::class);
        }

        $group->get('/me', function ($request, $response) {
            $response->getBody()->write(json_encode([
                'authorized' => true,
                'userId' => $request->getAttribute('authUserId'),
                'message' => 'Token auth passed',
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        })->add(AuthMiddleware::class);
    });
};
