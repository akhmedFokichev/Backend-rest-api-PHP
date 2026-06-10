<?php

declare(strict_types=1);

/**
 * Slim API (Backend-rest-api-PHP).
 *
 * Локально: API на http://localhost:8000 (public_html), админка на :8080.
 * mock_enabled: true — работа без запущенного API (демо-данные).
 */
return [
    'base_url' => 'http://localhost:8000/api/v1',
    'mock_enabled' => false,
    'timeout' => 15,
];
