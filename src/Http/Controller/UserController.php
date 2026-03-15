<?php

namespace App\Http\Controller;

use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserController
{
    /**
     * POST /user  Body: {"login": "...", "password": "..."}
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

        try {
            $user = new User();
            $user->login = $login;
            $user->setPassword($password);
            $user->save();
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'user already exists') {
                $response->getBody()->write(json_encode(['error' => 'user already exists']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }
            throw $e;
        }

        $response->getBody()->write(json_encode(['id' => $user->id, 'login' => $user->login]));
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

        $response->getBody()->write(json_encode([
            'id'      => $user->id,
            'login'   => $user->login,
            'message' => 'authorized',
        ]));
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
}
