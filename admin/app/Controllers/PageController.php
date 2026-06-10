<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;

final class PageController
{
    public function dashboard(): void
    {
        View::render('dashboard', [
            'title' => 'Дашборд',
            'pageTitle' => 'Дашборд',
            'user' => Auth::user(),
            'viewFile' => BASE_PATH . '/views/dashboard.php',
        ]);
    }

    public function usersIndex(): void
    {
        View::render('users/index', [
            'title' => 'Пользователи',
            'pageTitle' => 'Пользователи',
            'canDelete' => Auth::can('*'),
            'viewFile' => BASE_PATH . '/views/users/index.php',
        ]);
    }
}
