<?php

declare(strict_types=1);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

return [
    'base_url' => $scheme . '://' . $host . '/api/v1',
    'mock_enabled' => false,
    'timeout' => 15,
];
