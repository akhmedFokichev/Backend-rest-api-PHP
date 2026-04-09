<?php

use Medoo\Medoo;

/**
 * Создаёт подключение Medoo к MySQL. Подключать после config/db.php.
 * Использование: $db = (require __DIR__ . '/config/medoo.php')($dbConfig);
 */
return function (array $config): Medoo {
    return new Medoo([
        'type' => 'mysql',
        'host' => $config['host'],
        'database' => $config['dbname'],
        'username' => $config['user'],
        'password' => $config['pass'],
        'charset' => $config['charset'] ?? 'utf8mb4',
        'error' => PDO::ERRMODE_EXCEPTION,
    ]);
};
