# ⚙️ Конфигурация приложения

## 📍 Где указывать данные для подключения к БД

### config/env.local.php

**Это главный файл конфигурации!**

```php
<?php
return [
    'APP_NAME'   => 'Backend REST API',
    'APP_VERSION'=> '1.0.0',

    // Identity / JWT
    'HASH_KEY'   => 'your-hash-key',
    'CLIENT_IDS' => 'web_app,mobile_app',
    'JWT_SECRET' => 'your-jwt-secret',

    // Database ← ВОТ ЗДЕСЬ!
    'DB_HOST'    => 'localhost',
    'DB_NAME'    => 'cv82602_slimdev',
    'DB_USER'    => 'cv82602_slimdev',
    'DB_PASS'    => '4YxsN8Pp',
];
```

---

## 🚀 Установка конфигурации

### Локальная разработка

1. Скопируйте пример:
```bash
cp config/env.local.example.php config/env.local.php
```

2. Отредактируйте `config/env.local.php`:
```php
return [
    // Database
    'DB_HOST'    => 'localhost',
    'DB_NAME'    => 'your_local_db',
    'DB_USER'    => 'root',
    'DB_PASS'    => '',
];
```

### На сервере

Создайте файл `/home/c/cv82602/tradeApp/config/env.local.php`:

```php
<?php
return [
    'APP_NAME'   => 'Backend REST API',
    'APP_VERSION'=> '1.0.0',

    // Identity / JWT
    'HASH_KEY'   => 'your-random-hash-key-here',
    'CLIENT_IDS' => 'web_app,mobile_app',
    'JWT_SECRET' => 'your-random-jwt-secret-here',

    // Database
    'DB_HOST'    => 'localhost',
    'DB_NAME'    => 'cv82602_slimdev',
    'DB_USER'    => 'cv82602_slimdev',
    'DB_PASS'    => '4YxsN8Pp',
];
```

**⚠️ Важно:** Генерируйте случайные значения для `HASH_KEY` и `JWT_SECRET`!

---

## 🔐 Безопасность

### ✅ Защита файла конфигурации

1. **НЕ коммитьте** `env.local.php` в Git
   - Файл уже в `.gitignore`
   
2. **Установите права доступа** на сервере:
```bash
chmod 600 config/env.local.php
```

3. **Генерируйте случайные ключи:**
```php
// Пример генерации случайного ключа
echo bin2hex(random_bytes(32));
// Результат: 5f4dcc3b5aa765d61d8327deb882cf99a1b5f5e4e4e4e4e4e4e4e4e4e4e4e4e4
```

---

## 📋 Доступные параметры

### Приложение

| Параметр | Описание | По умолчанию |
|----------|----------|--------------|
| `APP_NAME` | Название приложения | `Puma` |
| `APP_VERSION` | Версия | `0.0.1` |

### Identity / JWT

| Параметр | Описание | По умолчанию |
|----------|----------|--------------|
| `HASH_KEY` | Ключ для хеширования | `change-me` |
| `CLIENT_IDS` | ID клиентов (через запятую) | `web_app` |
| `JWT_SECRET` | Секретный ключ JWT | `change-me-secret` |

### База данных

| Параметр | Описание | По умолчанию |
|----------|----------|--------------|
| `DB_HOST` | Хост БД | `localhost` |
| `DB_NAME` | Название БД | `` |
| `DB_USER` | Пользователь БД | `` |
| `DB_PASS` | Пароль БД | `` |

---

## 🔄 Как работает конфигурация

### 1. Загрузка конфигурации

```php
// public_html/index.php
$config = new \Config();
```

### 2. Config.php читает env.local.php

```php
// config/Config.php
private function loadLocalEnv(): array
{
    $path = __DIR__ . '/env.local.php';
    if (file_exists($path)) {
        $data = include $path;
        if (is_array($data)) {
            return $data;
        }
    }
    return [];
}
```

### 3. Использование в коде

```php
// Подключение к БД
$dsn = "mysql:host={$config->host};dbname={$config->db_name}";
$pdo = new PDO($dsn, $config->username, $config->password);

// JWT secret
$loginUC = new LoginUserUseCase(
    $userRepo, 
    $sessionRepo, 
    $passwords, 
    $config->clientIds[0], 
    $config->secretKey  // ← из конфига!
);
```

---

## 🌍 Разные конфигурации для окружений

### Локальная разработка

```php
// config/env.local.php (на вашем компьютере)
return [
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'myproject_local',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'JWT_SECRET' => 'dev-secret-not-for-production',
];
```

### Продакшн (сервер)

```php
// config/env.local.php (на сервере)
return [
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'cv82602_slimdev',
    'DB_USER' => 'cv82602_slimdev',
    'DB_PASS' => 'strong-password-here',
    'JWT_SECRET' => 'random-secure-secret-256-bits',
];
```

---

## ✅ Проверка конфигурации

### Создайте тестовый скрипт

```php
<?php
// test-config.php
require __DIR__ . '/config/Config.php';

$config = new Config();

echo "=== Конфигурация ===\n";
echo "App Name: {$config->appName}\n";
echo "Version: {$config->version}\n";
echo "DB Host: {$config->host}\n";
echo "DB Name: {$config->db_name}\n";
echo "DB User: {$config->username}\n";
echo "DB Pass: " . (strlen($config->password) > 0 ? "***" : "NOT SET") . "\n";
echo "JWT Secret: " . (strlen($config->secretKey) > 0 ? "***" : "NOT SET") . "\n";
echo "Client IDs: " . implode(', ', $config->clientIds) . "\n";

// Тест подключения к БД
try {
    $dsn = "mysql:host={$config->host};dbname={$config->db_name}";
    $pdo = new PDO($dsn, $config->username, $config->password);
    echo "\n✅ Подключение к БД успешно!\n";
} catch (PDOException $e) {
    echo "\n❌ Ошибка подключения к БД: " . $e->getMessage() . "\n";
}
```

Запустите:
```bash
php test-config.php
```

---

## 🆘 Устранение проблем

### Ошибка: "Access denied for user"

**Причина:** Неверные данные БД в конфигурации

**Решение:**
1. Проверьте `config/env.local.php`
2. Убедитесь что пользователь существует:
```sql
SELECT User, Host FROM mysql.user WHERE User = 'cv82602_slimdev';
```

### Ошибка: "Unknown database"

**Причина:** БД не создана

**Решение:**
1. Создайте БД:
```sql
CREATE DATABASE cv82602_slimdev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
2. Выполните миграции:
```bash
mysql -u cv82602_slimdev -p cv82602_slimdev < sql/install_all.sql
```

### Конфигурация не применяется

**Причина:** Файл `env.local.php` не существует

**Решение:**
1. Проверьте наличие файла:
```bash
ls -la config/env.local.php
```
2. Если нет - скопируйте из примера:
```bash
cp config/env.local.example.php config/env.local.php
```

---

## 📝 Чек-лист настройки

- [ ] Создан файл `config/env.local.php`
- [ ] Указаны данные БД (DB_HOST, DB_NAME, DB_USER, DB_PASS)
- [ ] Сгенерированы случайные ключи (HASH_KEY, JWT_SECRET)
- [ ] Права доступа 600 на `env.local.php`
- [ ] Файл НЕ закоммичен в Git
- [ ] Тестовое подключение к БД успешно
- [ ] API работает через Swagger UI

---

## 🎯 Итого

**Теперь данные БД хранятся в:**
```
config/env.local.php
```

**Не в:**
- ❌ public_html/index.php (больше НЕ хранятся там!)
- ❌ Git репозитории
- ❌ Открытых файлах

**Это безопасно и правильно!** ✅
