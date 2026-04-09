<?php

namespace App\Http\Middleware;

use App\Domain\Auth\UserToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');
        $token = $this->extractBearerToken($authHeader);

        if ($token === null) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing Authorization header',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }

        $tokenRow = UserToken::findActiveByPlainToken($token);
        if ($tokenRow === null) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'error' => 'Unauthorized',
                'message' => 'Token is invalid, expired or revoked',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }

        $request = $request->withAttribute('authUserId', $tokenRow['user_id']);
        return $handler->handle($request);
    }

    private function extractBearerToken(string $authHeader): ?string
    {
        $prefix = 'Bearer ';
        if (stripos($authHeader, $prefix) !== 0) {
            return null;
        }
        $token = trim((string) substr($authHeader, strlen($prefix)));
        return $token !== '' ? $token : null;
    }
}
