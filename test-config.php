<?php
/**
 * Тест конфигурации приложения
 * 
 * Проверяет:
 * - Загрузку config/env.local.php
 * - Подключение к БД
 * - Наличие необходимых параметров
 * 
 * Запуск: php test-config.php
 */

require __DIR__ . '/config/Config.php';

echo "╔═══════════════════════════════════════════╗\n";
echo "║    Проверка конфигурации приложения       ║\n";
echo "╚═══════════════════════════════════════════╝\n\n";

// Загрузка конфигурации
try {
    $config = new Config();
    echo "✅ Конфигурация загружена\n\n";
} catch (Exception $e) {
    echo "❌ Ошибка загрузки конфигурации: " . $e->getMessage() . "\n";
    exit(1);
}

// Вывод параметров
echo "📋 Параметры приложения:\n";
echo "   App Name:    {$config->appName}\n";
echo "   Version:     {$config->version}\n";
echo "   Client IDs:  " . implode(', ', $config->clientIds) . "\n\n";

echo "🔐 Безопасность:\n";
echo "   Hash Key:    " . (strlen($config->hashKey) > 10 ? "✅ Установлен" : "⚠️  Использует значение по умолчанию") . "\n";
echo "   JWT Secret:  " . (strlen($config->secretKey) > 10 ? "✅ Установлен" : "⚠️  Использует значение по умолчанию") . "\n\n";

echo "🗄️  База данных:\n";
echo "   Host:        {$config->host}\n";
echo "   Database:    " . ($config->db_name ?: "❌ НЕ УКАЗАНА") . "\n";
echo "   User:        " . ($config->username ?: "❌ НЕ УКАЗАН") . "\n";
echo "   Password:    " . (strlen($config->password) > 0 ? "✅ Установлен" : "❌ НЕ УКАЗАН") . "\n\n";

// Проверка наличия обязательных параметров
$errors = [];
if (!$config->db_name) {
    $errors[] = "DB_NAME не указан в config/env.local.php";
}
if (!$config->username) {
    $errors[] = "DB_USER не указан в config/env.local.php";
}
if (!$config->password) {
    $errors[] = "DB_PASS не указан в config/env.local.php";
}

if ($errors) {
    echo "❌ Ошибки конфигурации:\n";
    foreach ($errors as $error) {
        echo "   - {$error}\n";
    }
    echo "\n";
    echo "💡 Создайте файл config/env.local.php из примера:\n";
    echo "   cp config/env.local.example.php config/env.local.php\n";
    exit(1);
}

// Тест подключения к БД
echo "🔌 Тестирование подключения к БД...\n";

try {
    $dsn = "mysql:host={$config->host};dbname={$config->db_name};charset=utf8mb4";
    $pdo = new PDO($dsn, $config->username, $config->password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "   ✅ Подключение успешно!\n\n";
    
    // Проверка версии MySQL
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "   MySQL Version: {$version}\n\n";
    
    // Проверка таблиц
    echo "📊 Проверка таблиц:\n";
    $tables = ['identity_users', 'identity_session', 'ref_country', 'storage_files'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->fetch()) {
            // Подсчет записей
            $count = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            echo "   ✅ {$table} (записей: {$count})\n";
        } else {
            echo "   ❌ {$table} (не найдена)\n";
        }
    }
    
    echo "\n";
    
} catch (PDOException $e) {
    echo "   ❌ Ошибка подключения: " . $e->getMessage() . "\n\n";
    echo "💡 Проверьте:\n";
    echo "   1. Данные БД в config/env.local.php\n";
    echo "   2. Существует ли база данных '{$config->db_name}'\n";
    echo "   3. Права доступа пользователя '{$config->username}'\n";
    exit(1);
}

// Итог
echo "╔═══════════════════════════════════════════╗\n";
echo "║         ✅ Конфигурация корректна!        ║\n";
echo "╚═══════════════════════════════════════════╝\n\n";

echo "🚀 Приложение готово к работе!\n";
echo "   Swagger UI: http://localhost/swagger-ui.html\n";
echo "   или https://tradeapp.xsdk.ru/swagger-ui.html\n";
