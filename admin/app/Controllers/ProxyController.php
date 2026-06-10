<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ApiClient;
use App\Core\Auth;
use App\Core\Url;
use App\Core\View;

/**
 * Same-origin proxy to Slim API. JS calls /api/proxy/products instead of external API URL.
 */
final class ProxyController
{
    public function handle(): void
    {
        if (!Auth::check()) {
            View::json(['message' => 'Unauthorized'], 401);
        }

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
        $prefix = Url::to('api/proxy/');
        $path = str_starts_with($uri, $prefix) ? substr($uri, strlen($prefix)) : '';

        if ($path === '') {
            View::json(['message' => 'Missing API path'], 400);
        }

        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $body = null;

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw ?: '', true);
            $body = is_array($decoded) ? $decoded : null;
        }

        $client = new ApiClient();
        $response = $client->request($method, $path, $body, Auth::token());

        $status = (int) ($response['_status'] ?? 500);
        unset($response['_status']);

        if ($status === 204) {
            http_response_code(204);
            exit;
        }

        View::json($response, $status);
    }
}
