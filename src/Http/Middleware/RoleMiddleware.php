<?php

namespace App\Http\Middleware;

use App\Domain\User\User;
use App\Enum\Role;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class RoleMiddleware implements MiddlewareInterface
{
    public function __construct(private Role $minRole) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authUserId = (int) ($request->getAttribute('authUserId') ?? 0);
        $authUser = $authUserId > 0 ? User::findById($authUserId) : null;

        if ($authUser === null) {
            return $this->deny('User for token not found');
        }

        if (!$authUser->role->atLeast($this->minRole)) {
            return $this->deny(
                'Insufficient role',
                [
                    'requiredRole' => $this->minRole->value,
                    'requiredRoleLabel' => $this->minRole->label(),
                    'currentRole' => $authUser->role->value,
                    'currentRoleLabel' => $authUser->role->label(),
                ]
            );
        }

        return $handler->handle($request);
    }

    private function deny(string $message, array $extra = []): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode(array_merge([
            'error' => 'Forbidden',
            'message' => $message,
        ], $extra)));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
    }
}
