<?php

namespace App\Http\Controller;

use App\Domain\Auth\UserToken;
use App\Domain\Profile\Profile;
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
        [$login, $password] = $this->extractCredentials($request);

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

            // Создаем профиль для нового пользователя
            $profile = new Profile();
            $profile->userId = $user->id;
            $profile->save();

        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'user already exists') {
                $response->getBody()->write(json_encode(['error' => 'user already exists']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }
            $response->getBody()->write(json_encode([
                'error' => 'failed to create user',
                'details' => $e->getPrevious()?->getMessage(),
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $response->getBody()->write(json_encode($user->toArray()));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    /**
     * POST /user/login  Body: {"login": "...", "password": "..."}
     */
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        [$login, $password] = $this->extractCredentials($request);

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
     * GET /user
     */
    public function list(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $users = array_map(
            static fn(User $user): array => $user->toArray(),
            User::findAll()
        );

        $response->getBody()->write(json_encode([
            'items' => $users,
            'count' => count($users),
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

    /**
     * Поддерживает JSON body, parsed body (form-data/x-www-form-urlencoded) и query params.
     *
     * @return array{0: string, 1: string}
     */
    private function extractCredentials(ServerRequestInterface $request): array
    {
        $jsonBody = json_decode((string) $request->getBody(), true);
        $parsedBody = $request->getParsedBody();
        $queryParams = $request->getQueryParams();

        $sources = [
            is_array($jsonBody) ? $jsonBody : [],
            is_array($parsedBody) ? $parsedBody : [],
            is_array($queryParams) ? $queryParams : [],
        ];

        foreach ($sources as $source) {
            $login = trim((string) ($source['login'] ?? ''));
            $password = (string) ($source['password'] ?? '');
            if ($login !== '' && $password !== '') {
                return [$login, $password];
            }
        }

        return ['', ''];
    }
}
