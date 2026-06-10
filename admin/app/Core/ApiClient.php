<?php

declare(strict_types=1);

namespace App\Core;

final class ApiClient
{
    /** @var array<string, mixed> */
    private array $config;

    public function __construct()
    {
        $this->config = require BASE_PATH . '/app/Config/api.php';
    }

    public function isMock(): bool
    {
        return (bool) ($this->config['mock_enabled'] ?? false);
    }

    /** @param array<string, mixed>|null $body */
    public function request(string $method, string $path, ?array $body = null, ?string $token = null): array
    {
        if ($this->isMock()) {
            return $this->mock($method, $path, $body);
        }

        $url = rtrim((string) $this->config['base_url'], '/') . '/' . ltrim($path, '/');

        $ch = curl_init($url);

        if ($ch === false) {
            return $this->error('Failed to initialize HTTP client');
        }

        $headers = ['Accept: application/json', 'Content-Type: application/json'];

        if ($token !== null) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => (int) ($this->config['timeout'] ?? 15),
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
        }

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return $this->error('API request failed: ' . $curlError, $status ?: 502);
        }

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            return $this->error('Invalid JSON response from API', $status ?: 502);
        }

        $decoded['_status'] = $status;
        return $decoded;
    }

    /** @param array<string, mixed>|null $body */
    private function mock(string $method, string $path, ?array $body): array
    {
        $method = strtoupper($method);
        $path = trim($path, '/');

        if ($path === 'user/login' && $method === 'POST') {
            $login = (string) ($body['login'] ?? '');
            $password = (string) ($body['password'] ?? '');

            if ($login === 'admin@example.com' && $password === 'admin') {
                return [
                    '_status' => 200,
                    'id' => 1,
                    'login' => $login,
                    'role' => 100,
                    'roleLabel' => 'Администратор',
                    'accessToken' => 'mock-token-admin',
                    'tokenType' => 'Bearer',
                    'message' => 'authorized',
                ];
            }

            return ['_status' => 401, 'error' => 'invalid credentials'];
        }

        if ($path === 'me' && $method === 'GET') {
            return [
                '_status' => 200,
                'authorized' => true,
                'userId' => 1,
                'message' => 'Token auth passed',
            ];
        }

        if ($path === 'user/list' && $method === 'GET') {
            return [
                '_status' => 200,
                'items' => [
                    ['id' => 1, 'login' => 'admin@example.com', 'role' => 100, 'roleLabel' => 'Администратор'],
                    ['id' => 2, 'login' => 'user@example.com', 'role' => 10, 'roleLabel' => 'Пользователь'],
                ],
                'count' => 2,
            ];
        }

        if (preg_match('#^user/(\d+)$#', $path) && $method === 'DELETE') {
            return ['_status' => 204, 'data' => null];
        }

        return $this->error('Mock endpoint not found: ' . $path, 404);
    }

    /** @return array<string, mixed> */
    private function error(string $message, int $status = 400): array
    {
        return [
            '_status' => $status,
            'message' => $message,
        ];
    }
}
