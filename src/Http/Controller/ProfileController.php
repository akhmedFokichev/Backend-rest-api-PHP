<?php

namespace App\Http\Controller;

use App\Domain\User\User;
use App\Domain\Profile\Profile;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProfileController
{
    /**
     * GET /user/{id}/profile — получить профиль пользователя.
     */
    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $userId = (int) ($args['id'] ?? 0);
        if ($userId <= 0) {
            $response->getBody()->write(json_encode(['error' => 'invalid user id']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (User::findById($userId) === null) {
            $response->getBody()->write(json_encode(['error' => 'user not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $profile = Profile::findByUserId($userId);
        if ($profile === null) {
            $response->getBody()->write(json_encode(['error' => 'profile not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode($profile->toArray()));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * PUT /user/{id}/profile — создать или обновить профиль пользователя.
     * Body: {"firstName": "...", "lastName": "...", "phone": "...", "avatarUrl": "..."}
     */
    public function save(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $userId = (int) ($args['id'] ?? 0);
        if ($userId <= 0) {
            $response->getBody()->write(json_encode(['error' => 'invalid user id']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (User::findById($userId) === null) {
            $response->getBody()->write(json_encode(['error' => 'user not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $body = json_decode((string) $request->getBody(), true) ?: [];

        $profile = Profile::findByUserId($userId);
        if ($profile === null) {
            $profile = new Profile();
            $profile->userId = $userId;
        }

        if (array_key_exists('firstName', $body)) {
            $profile->firstName = $body['firstName'] !== null && $body['firstName'] !== '' ? (string) $body['firstName'] : null;
        }
        if (array_key_exists('lastName', $body)) {
            $profile->lastName = $body['lastName'] !== null && $body['lastName'] !== '' ? (string) $body['lastName'] : null;
        }
        if (array_key_exists('phone', $body)) {
            $profile->phone = $body['phone'] !== null && $body['phone'] !== '' ? (string) $body['phone'] : null;
        }
        if (array_key_exists('avatarUrl', $body)) {
            $profile->avatarUrl = $body['avatarUrl'] !== null && $body['avatarUrl'] !== '' ? (string) $body['avatarUrl'] : null;
        }

        $profile->save();

        $response->getBody()->write(json_encode($profile->toArray()));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    /**
     * DELETE /user/{id}/profile — удалить профиль пользователя.
     */
    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $userId = (int) ($args['id'] ?? 0);
        if ($userId <= 0) {
            $response->getBody()->write(json_encode(['error' => 'invalid user id']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $profile = Profile::findByUserId($userId);
        if ($profile === null) {
            $response->getBody()->write(json_encode(['error' => 'profile not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $profile->delete();
        return $response->withStatus(204);
    }
}
