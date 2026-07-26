<?php

/**
 * PageController.php — страницы и HTML-фрагменты админ-панели.
 *
 * Назначение: SPA-shell + fragment-эндпоинты для подгрузки только content.
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

    public function fragmentDashboard(): void
    {
        View::render('screens/dashboard', [], '');
    }

    public function fragmentUsers(): void
    {
        View::render('screens/users', [], '');
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
                'fragments' => [
                    'dashboard' => Url::to('fragment/dashboard'),
                    'users' => Url::to('fragment/users'),
                ],
            ],
            'viewFile' => BASE_PATH . '/views/shell.php',
        ]);
    }
}
