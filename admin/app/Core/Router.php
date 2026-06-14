<?php

/**
 * Router.php — простой маршрутизатор админки.
 *
 * Назначение: сопоставляет URL и HTTP-метод с контроллером и middleware.
 */

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, array{handler: callable, middleware: list<callable>}>> */
    private array $routes = [];

    public function get(string $path, callable $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, callable $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rtrim($path, '/') ?: '/';

        $route = $this->routes[$method][$path] ?? null;

        if ($route === null) {
            http_response_code(404);
            View::render('errors/404', [
                'title' => 'Страница не найдена',
                'guestBodyClass' => 'login-page quokka-login',
                'viewFile' => BASE_PATH . '/views/errors/404.php',
            ], 'guest');
            return;
        }

        foreach ($route['middleware'] as $middleware) {
            $middleware();
        }

        ($route['handler'])();
    }

    private function add(string $method, string $path, callable $handler, array $middleware): void
    {
        $path = rtrim($path, '/') ?: '/';
        $this->routes[$method][$path] = [
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }
}
