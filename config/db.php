<?php

/**
 * Настройки БД. Локальные значения — в config/db.local.php (не в git).
 */

$local = __DIR__ . '/db.local.php';
$defaults = [
    'host'    => 'localhost',
    'dbname'  => '',
    'user'    => '',
    'pass'    => '',
    'charset' => 'utf8mb4',
];

if (is_file($local)) {
    return array_merge($defaults, require $local);
}

return $defaults;
