<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ApiClient;
use App\Core\Auth;
use App\Core\View;

final class AuthController
{
    public function showLogin(): void
    {
        View::render('auth/login', [
            'title' => 'Вход',
            'csrf' => Auth::csrfToken(),
            'error' => $_SESSION['login_error'] ?? null,
        ], 'guest');

        unset($_SESSION['login_error']);
    }

    public function login(): void
    {
        if (!Auth::verifyCsrf($_POST['csrf'] ?? null)) {
            $_SESSION['login_error'] = 'Неверный CSRF-токен. Обновите страницу.';
            View::redirect('/login');
        }

        $login = trim((string) ($_POST['login'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $client = new ApiClient();
        $response = $client->request('POST', 'user/login', [
            'login' => $login,
            'password' => $password,
        ]);

        $status = (int) ($response['_status'] ?? 500);
        $token = (string) ($response['accessToken'] ?? $response['token'] ?? '');

        if ($status !== 200 || $token === '') {
            $_SESSION['login_error'] = (string) ($response['error'] ?? $response['message'] ?? 'Ошибка входа');
            View::redirect('/login');
        }

        $role = (int) ($response['role'] ?? 0);

        Auth::login($token, [
            'id' => $response['id'] ?? null,
            'login' => $response['login'] ?? $login,
            'name' => $response['roleLabel'] ?? ($response['login'] ?? $login),
            'role' => $role,
            'roleLabel' => $response['roleLabel'] ?? '',
            'permissions' => Auth::permissionsFromRole($role),
        ]);

        View::redirect('/admin');
    }

    public function logout(): void
    {
        $token = Auth::token();

        if ($token !== null) {
            $client = new ApiClient();
            $client->request('POST', 'user/logout', null, $token);
        }

        Auth::logout();
        View::redirect('/login');
    }
}
