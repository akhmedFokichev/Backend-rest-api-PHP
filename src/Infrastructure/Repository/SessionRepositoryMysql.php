<?php
namespace App\Infrastructure\Repository;

use App\Domain\Identity\Repository\SessionRepositoryInterface;
use PDO;

class SessionRepositoryMysql implements SessionRepositoryInterface
{
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function create(int $userId, string $accessToken, string $refreshToken, int $expiresIn, string $clientId, string $secretKey): void
    {
        $sql = 'INSERT INTO identity_session (user_id, access_token, refresh_token, expiresIn, client_id, secret_key, updated_at, created_at) VALUES (:user_id, :access_token, :refresh_token, :expiresIn, :client_id, :secret_key, NOW(), NOW())';
        $stm = $this->pdo->prepare($sql);
        $stm->execute([
            ':user_id' => $userId,
            ':access_token' => $accessToken,
            ':refresh_token' => $refreshToken,
            ':expiresIn' => $expiresIn,
            ':client_id' => $clientId,
            ':secret_key' => $secretKey,
        ]);
    }

    public function updateByRefreshToken(int $userId, string $oldRefreshToken, string $newAccessToken, string $newRefreshToken, int $expiresIn): void
    {
        $sql = 'UPDATE identity_session SET access_token = :access_token, refresh_token = :refresh_token, expiresIn = :expiresIn, updated_at = NOW() WHERE user_id = :user_id AND refresh_token = :old_refresh';
        $stm = $this->pdo->prepare($sql);
        $stm->execute([
            ':access_token' => $newAccessToken,
            ':refresh_token' => $newRefreshToken,
            ':expiresIn' => $expiresIn,
            ':user_id' => $userId,
            ':old_refresh' => $oldRefreshToken,
        ]);
    }

    public function findByTokens(string $accessToken, string $refreshToken): ?array
    {
        $sql = 'SELECT * FROM identity_session WHERE access_token = :access AND refresh_token = :refresh LIMIT 1';
        $stm = $this->pdo->prepare($sql);
        $stm->execute([':access' => $accessToken, ':refresh' => $refreshToken]);
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}


