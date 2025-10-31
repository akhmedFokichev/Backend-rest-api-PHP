<?php
namespace App\Domain\Identity\Repository;

interface SessionRepositoryInterface
{
    public function create(int $userId, string $accessToken, string $refreshToken, int $expiresIn, string $clientId, string $secretKey): void;
    public function updateByRefreshToken(int $userId, string $oldRefreshToken, string $newAccessToken, string $newRefreshToken, int $expiresIn): void;
    public function findByTokens(string $accessToken, string $refreshToken): ?array;
}


