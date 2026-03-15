<?php

use Slim\App;
use App\Http\Middleware\AuthMiddleware;

return function (App $app): void {
    // Публичный маршрут (без авторизации)
    $app->get('/', function ($request, $response) {
        $response->getBody()->write('OK');
        return $response->withHeader('Content-Type', 'text/plain');
    });

    // Защищённые маршруты — требуют заголовок Authorization: Bearer mock-token
    $app->group('/api', function (\Slim\Routing\RouteCollectorProxy $group) {
        $group->get('/me', function ($request, $response) {
            $response->getBody()->write(json_encode(['authorized' => true, 'message' => 'Mock auth passed']));
            return $response->withHeader('Content-Type', 'application/json');
        });
    })->add(AuthMiddleware::class);
};
