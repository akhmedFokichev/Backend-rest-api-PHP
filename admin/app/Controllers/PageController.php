<?php

/**
 * PageController.php — страницы админ-панели.
 *
 * Назначение: дашборд и список пользователей (рендер views).
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;

final class PageController
{
    public function dashboard(): void
    {
        View::render('dashboard', [
            'title' => 'Главная — Quokka Admin',
            'pageTitle' => 'Главная',
            'pageSubtitle' => 'Обзор и быстрые действия',
            'user' => Auth::user(),
            'viewFile' => BASE_PATH . '/views/dashboard.php',
        ]);
    }

    public function usersIndex(): void
    {
        View::render('users/index', [
            'title' => 'Пользователи — Quokka Admin',
            'pageTitle' => 'Пользователи',
            'pageSubtitle' => 'Управление учётными записями',
            'canDelete' => Auth::can('*'),
            'viewFile' => BASE_PATH . '/views/users/index.php',
        ]);
    }
}
