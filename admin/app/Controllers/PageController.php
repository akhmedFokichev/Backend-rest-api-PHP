<?php

/**
 * PageController.php — страницы админ-панели.
 *
 * Назначение: отдаёт SPA-shell; экраны переключает Alpine AppStore без reload.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Url;
use App\Core\View;

final class PageController
{
    public function dashboard(): void
    {
        $this->renderShell('dashboard');
    }

    public function usersIndex(): void
    {
        $this->renderShell('users');
    }

    private function renderShell(string $route): void
    {
        $isUsers = $route === 'users';

        View::render('shell', [
            'title' => $isUsers ? 'Пользователи — Quokka Admin' : 'Главная — Quokka Admin',
            'pageTitle' => $isUsers ? 'Пользователи' : 'Главная',
            'pageSubtitle' => $isUsers ? 'Управление учётными записями' : 'Статистика платформы',
            'initialRoute' => $route,
            'user' => Auth::user(),
            'boot' => [
                'route' => $route,
                'user' => Auth::user(),
                'canViewUsers' => Auth::can('users.view'),
                'canDelete' => Auth::can('*'),
                'paths' => [
                    'dashboard' => Url::to(),
                    'users' => Url::to('users'),
                ],
            ],
            'viewFile' => BASE_PATH . '/views/shell.php',
        ]);
    }
}
