<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    /** @param array<string, mixed> $data */
    public static function render(string $view, array $data = [], string $layout = 'admin'): void
    {
        $viewFile = BASE_PATH . '/views/' . $view . '.php';
        $data['viewFile'] = $data['viewFile'] ?? $viewFile;
        extract($data, EXTR_SKIP);

        if (!is_file($viewFile)) {
            http_response_code(500);
            echo 'View not found: ' . htmlspecialchars($view);
            return;
        }

        if ($layout === '') {
            require $viewFile;
            return;
        }

        $layoutFile = BASE_PATH . '/views/layouts/' . $layout . '.php';

        if (!is_file($layoutFile)) {
            http_response_code(500);
            echo 'Layout not found: ' . htmlspecialchars($layout);
            return;
        }

        require $layoutFile;
    }

    public static function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }

    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
