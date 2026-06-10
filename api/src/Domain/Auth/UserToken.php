<?php

namespace App\Domain\Auth;

use Medoo\Medoo;

class UserToken
{
    private static ?Medoo $db = null;

    public static function setDb(Medoo $db): void
    {
        self::$db = $db;
    }

    private static function db(): Medoo
    {
        if (self::$db === null) {
            throw new \RuntimeException('UserToken::setDb() was not called');
        }
        return self::$db;
    }

    /**
     * Выпустить random bearer token для пользователя.
     * Возвращает открытый токен (только для ответа клиенту) и expiresAt.
     *
     * @return array{token:string, expiresAt:string}
     */
    public static function issue(int $userId, int $ttlSeconds = 2592000): array
    {
        $token = bin2hex(random_bytes(32)); // 64 hex chars
        $tokenHash = hash('sha256', $token);
        $expiresAt = (new \DateTimeImmutable())
            ->modify('+' . $ttlSeconds . ' seconds')
            ->format('Y-m-d H:i:s');

        self::db()->insert('user_token', [
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);

        return ['token' => $token, 'expiresAt' => $expiresAt];
    }

    /**
     * Найти активный токен по открытому bearer token.
     *
     * @return array{id:int,user_id:int,expires_at:?string}|null
     */
    public static function findActiveByPlainToken(string $plainToken): ?array
    {
        $tokenHash = hash('sha256', $plainToken);
        $row = self::db()->get('user_token', ['id', 'user_id', 'expires_at'], [
            'token_hash' => $tokenHash,
            'revoked_at' => null,
            'AND' => [
                'OR' => [
                    'expires_at[>]' => date('Y-m-d H:i:s'),
                    'expires_at' => null,
                ],
            ],
        ]);
        if (!$row) {
            return null;
        }
        return [
            'id' => (int) $row['id'],
            'user_id' => (int) $row['user_id'],
            'expires_at' => $row['expires_at'] ?? null,
        ];
    }

    public static function revokeByPlainToken(string $plainToken): void
    {
        $tokenHash = hash('sha256', $plainToken);
        self::db()->update('user_token', [
            'revoked_at' => Medoo::raw('CURRENT_TIMESTAMP'),
        ], [
            'token_hash' => $tokenHash,
            'revoked_at' => null,
        ]);
    }
}
