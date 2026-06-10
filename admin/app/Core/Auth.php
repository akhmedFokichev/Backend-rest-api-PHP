<?php

/**
 * Auth.php — сессия и права пользователя админки.
 *
 * Назначение: хранит токен API в $_SESSION, проверяет login/logout и permissions по роли.
 */

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['api_token']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function token(): ?string
    {
        return $_SESSION['api_token'] ?? null;
    }

    public static function login(string $token, array $user): void
    {
        $_SESSION['api_token'] = $token;
        $_SESSION['user'] = $user;
    }

    public static function logout(): void
    {
        unset($_SESSION['api_token'], $_SESSION['user']);
    }

    public static function can(string $permission): bool
    {
        $permissions = $_SESSION['user']['permissions'] ?? [];
        return in_array($permission, $permissions, true) || in_array('*', $permissions, true);
    }

    /** Права UI из числовой роли Slim API (Role enum). */
    public static function permissionsFromRole(int $role): array
    {
        if ($role >= 100) {
            return ['*'];
        }

        if ($role >= 50) {
            return ['users.view', 'profile.view'];
        }

        if ($role >= 10) {
            return ['profile.view'];
        }

        return [];
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            View::redirect('/login');
        }
    }

    public static function requireGuest(): void
    {
        if (self::check()) {
            View::redirect('/admin');
        }
    }

    public static function requirePermission(string $permission): void
    {
        self::requireAuth();

        if (!self::can($permission)) {
            http_response_code(403);
            View::render('errors/403', [], 'guest');
            exit;
        }
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        return is_string($token)
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}
