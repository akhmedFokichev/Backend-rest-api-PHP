<?php

/**
 * Создаёт подключение PDO к MySQL. Подключать после config/db.php.
 * Использование: $pdo = (require __DIR__ . '/config/pdo.php')($dbConfig);
 */

return function (array $config): PDO {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['dbname'],
        $config['charset'] ?? 'utf8mb4'
    );

    return new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
};
