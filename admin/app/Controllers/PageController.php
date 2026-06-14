<?php

/**
 * PageController.php — страницы админ-панели.
 *
 * Назначение: дашборд и список пользователей (рендер views).
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ApiClient;
use App\Core\Auth;
use App\Core\View;

final class PageController
{
    public function dashboard(): void
    {
        View::render('dashboard', [
            'title' => 'Главная — Quokka Admin',
            'pageTitle' => 'Главная',
            'pageSubtitle' => 'Статистика платформы',
            'user' => Auth::user(),
            'stats' => $this->fetchDashboardStats(),
            'canViewUsers' => Auth::can('users.view'),
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

    /** @return array{usersTotal: ?int, usersAdmin: ?int, usersModerator: ?int, usersRegular: ?int, apiOk: ?bool} */
    private function fetchDashboardStats(): array
    {
        $stats = [
            'usersTotal' => null,
            'usersAdmin' => null,
            'usersModerator' => null,
            'usersRegular' => null,
            'apiOk' => null,
        ];

        $client = new ApiClient();

        $dbCheck = $client->request('GET', 'db-check');
        $stats['apiOk'] = ((int) ($dbCheck['_status'] ?? 0) === 200 && ($dbCheck['ok'] ?? false) === true);

        if (!Auth::can('users.view')) {
            return $stats;
        }

        $token = Auth::token();
        if ($token === null) {
            return $stats;
        }

        $response = $client->request('GET', 'user/list', null, $token);
        if ((int) ($response['_status'] ?? 0) !== 200) {
            return $stats;
        }

        $items = $response['items'] ?? [];
        if (!is_array($items)) {
            return $stats;
        }

        $stats['usersTotal'] = count($items);
        $stats['usersAdmin'] = 0;
        $stats['usersModerator'] = 0;
        $stats['usersRegular'] = 0;

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $role = (int) ($item['role'] ?? 0);

            if ($role >= 100) {
                $stats['usersAdmin']++;
            } elseif ($role >= 50) {
                $stats['usersModerator']++;
            } elseif ($role >= 10) {
                $stats['usersRegular']++;
            }
        }

        return $stats;
    }
}
