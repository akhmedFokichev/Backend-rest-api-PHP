<?php

namespace App\Http\Controller;

use App\Domain\Auth\UserToken;
use App\Domain\User\User;
use App\Enum\Role;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserController
{
    /**
     * POST /user  Body: {"login": "...", "password": "...", "role": 10}  (role опционально, по умолчанию 10=User)
     */
    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = json_decode((string) $request->getBody(), true) ?: [];
        $login = trim((string) ($body['login'] ?? ''));
        $password = (string) ($body['password'] ?? '');

        if ($login === '' || $password === '') {
            $response->getBody()->write(json_encode(['error' => 'login and password are required']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $role = Role::User;

        try {
            $user = new User();
            $user->login = $login;
            $user->setPassword($password);
            $user->role = $role;
            $user->save();
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'user already exists') {
                $response->getBody()->write(json_encode(['error' => 'user already exists']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }
            throw $e;
        }

        $response->getBody()->write(json_encode($user->toArray()));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    /**
     * POST /user/login  Body: {"login": "...", "password": "..."}
     */
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = json_decode((string) $request->getBody(), true) ?: [];
        $login = trim((string) ($body['login'] ?? ''));
        $password = (string) ($body['password'] ?? '');

        if ($login === '' || $password === '') {
            $response->getBody()->write(json_encode(['error' => 'login and password are required']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $user = User::findByLogin($login);
        if ($user === null || !$user->verifyPassword($password)) {
            $response->getBody()->write(json_encode(['error' => 'invalid credentials']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
        $issued = UserToken::issue((int) $user->id);

        $response->getBody()->write(json_encode([
            'id'        => $user->id,
            'login'     => $user->login,
            'role'      => $user->role->value,
            'roleLabel' => $user->role->label(),
            'accessToken' => $issued['token'],
            'tokenType' => 'Bearer',
            'expiresAt' => $issued['expiresAt'],
            'message'   => 'authorized',
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * POST /user/logout
     * Требует Authorization: Bearer <token>
     */
    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $token = $this->extractBearerToken($request->getHeaderLine('Authorization'));
        if ($token === null) {
            $response->getBody()->write(json_encode(['error' => 'missing bearer token']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        UserToken::revokeByPlainToken($token);
        $response->getBody()->write(json_encode(['ok' => true, 'message' => 'logged out']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * DELETE /user/{id}
     */
    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) ($args['id'] ?? 0);
        if ($id <= 0) {
            $response->getBody()->write(json_encode(['error' => 'invalid id']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $user = User::findById($id);
        if ($user === null) {
            $response->getBody()->write(json_encode(['error' => 'user not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $user->delete();
        return $response->withStatus(204);
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
