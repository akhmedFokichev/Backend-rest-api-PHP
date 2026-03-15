<?php

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class AuthMiddleware implements MiddlewareInterface
{
    /** Мок: допустимый токен (позже заменить на проверку JWT/сессии) */
    private const MOCK_VALID_TOKEN = 'mock-token';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if ($authHeader === '' || !$this->mockCheck($authHeader)) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing Authorization header',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }

        return $handler->handle($request);
    }

    /**
     * Мок-проверка: считаем авторизованным, если передан Bearer mock-token.
     * Позже заменить на проверку JWT или сессии.
     */
    private function mockCheck(string $authHeader): bool
    {
        $prefix = 'Bearer ';
        if (stripos($authHeader, $prefix) !== 0) {
            return false;
        }
        $token = trim(substr($authHeader, strlen($prefix)));
        return $token === self::MOCK_VALID_TOKEN;
    }
}
