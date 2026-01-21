# 🚀 Чек-лист деплоя на сервер

## ❗ Текущая проблема

Сервер возвращает **HTTP 500** - это значит есть ошибка в PHP или проблема с БД.

---

## ✅ Шаги для исправления

### ШАГ 1: Обновите config/env.local.php на сервере

**Через FTP/SFTP загрузите файл:**
```
config/env.local.php
```

В путь на сервере:
```
/home/c/cv82602/tradeApp/config/env.local.php
```

**Или создайте через SSH:**
```bash
ssh cv82602@tradeapp.xsdk.ru
cd /home/c/cv82602/tradeApp
nano config/env.local.php
```

Вставьте:
```php
<?php

// Конфигурация приложения
// НЕ КОММИТЬТЕ этот файл в Git!
return [
    'APP_NAME'   => 'Backend REST API',
    'APP_VERSION'=> '1.0.0',

    // Identity / JWT
    'HASH_KEY'   => 'your-hash-key-change-me',
    'CLIENT_IDS' => 'web_app,mobile_app',
    'JWT_SECRET' => 'your-jwt-secret-change-me-to-random-string',

    // Database - НОВЫЕ ДАННЫЕ!
    'DB_HOST'    => 'localhost',
    'DB_NAME'    => 'cv82602_trade',
    'DB_USER'    => 'cv82602_trade',
    'DB_PASS'    => 'CHW423Br',
];
```

Сохраните: `Ctrl+O`, `Enter`, `Ctrl+X`

---

### ШАГ 2: Создайте таблицы в новой базе данных

**Через SSH:**
```bash
mysql -u cv82602_trade -p cv82602_trade < sql/install_all.sql
# Введите пароль: CHW423Br
```

**Или через phpMyAdmin:**
1. Откройте phpMyAdmin
2. Выберите базу `cv82602_trade`
3. Вкладка **SQL**
4. Скопируйте содержимое файла `sql/install_all.sql`
5. Вставьте и нажмите **Выполнить**

---

### ШАГ 3: Проверьте что таблицы созданы

**Через SSH:**
```bash
mysql -u cv82602_trade -p cv82602_trade -e "SHOW TABLES;"
```

**Должны быть 4 таблицы:**
- identity_users
- identity_session
- ref_country
- storage_files

---

### ШАГ 4: Обновите код на сервере

**Загрузите через FTP/SFTP:**

```
public_html/index.php          (обновлен - использует Config)
src/Http/routes.php            (добавлен Storage)
src/Http/OpenApi.php           (добавлен тег Storage)
src/Http/Controller/Storage/   (новый модуль)
src/Domain/Storage/            (новый модуль)
src/Application/Storage/       (новый модуль)
src/Infrastructure/Storage/    (новый модуль)
```

---

### ШАГ 5: Создайте директорию storage

**Через SSH:**
```bash
cd /home/c/cv82602/tradeApp
mkdir -p storage/files
chmod -R 755 storage
```

**Или через FTP:**
- Создайте папку `storage/files/`
- Установите права: 755

---

### ШАГ 6: Проверьте права доступа

```bash
# Проверка config
ls -la /home/c/cv82602/tradeApp/config/env.local.php

# Проверка storage
ls -la /home/c/cv82602/tradeApp/storage/

# Если нужно, установите права
chmod 600 /home/c/cv82602/tradeApp/config/env.local.php
chmod -R 755 /home/c/cv82602/tradeApp/storage/
```

---

### ШАГ 7: Проверьте PHP логи

**Посмотрите логи ошибок:**
```bash
tail -f /home/c/cv82602/logs/error.log
# или
tail -f /var/log/php_errors.log
```

Это покажет точную ошибку!

---

### ШАГ 8: Тестовая проверка конфигурации

**Через SSH:**
```bash
cd /home/c/cv82602/tradeApp
php test-config.php
```

**Должно быть:**
```
✅ Конфигурация загружена
✅ Подключение к БД успешно!
✅ identity_users (записей: 0)
✅ identity_session (записей: 0)
✅ ref_country (записей: 8)
✅ storage_files (записей: 0)
```

---

### ШАГ 9: Проверка API

**Откройте в браузере:**
```
https://tradeapp.xsdk.ru/
```
**Должно быть:** `OK`

**Swagger UI:**
```
https://tradeapp.xsdk.ru/swagger-ui.html
```
**Должны быть 3 секции:** Identity, Reference, Storage

---

### ШАГ 10: Полное тестирование API

**На вашем компьютере запустите:**
```bash
cd /Users/azapsh/Documents/Projects/backend/Backend-rest-api-PHP
./test-all-api.sh https://tradeapp.xsdk.ru
```

---

## 🔍 Диагностика проблем

### Ошибка: "Access denied for user"

**Причина:** Неверные данные БД

**Решение:**
1. Проверьте `config/env.local.php` на сервере
2. Проверьте что пользователь `cv82602_trade` существует:
```bash
mysql -u cv82602_trade -pCHW423Br -e "SELECT 1;"
```

### Ошибка: "Unknown database"

**Причина:** База `cv82602_trade` не существует

**Решение:**
```bash
mysql -u root -p -e "CREATE DATABASE cv82602_trade CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Ошибка: "Table doesn't exist"

**Причина:** Таблицы не созданы

**Решение:**
```bash
mysql -u cv82602_trade -pCHW423Br cv82602_trade < sql/install_all.sql
```

### HTTP 500 продолжается

**Решение:**
1. Смотрите PHP error log:
```bash
tail -100 /home/c/cv82602/logs/error.log
```

2. Включите отладку (временно) в `public_html/index.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

---

## 📋 Финальный чек-лист

- [ ] ✅ Файл `config/env.local.php` обновлен на сервере
- [ ] ✅ База данных `cv82602_trade` создана
- [ ] ✅ Пользователь `cv82602_trade` имеет доступ
- [ ] ✅ Таблицы созданы (4 штуки)
- [ ] ✅ Код обновлен (модуль Storage)
- [ ] ✅ Директория `storage/files/` создана
- [ ] ✅ Права доступа установлены
- [ ] ✅ `https://tradeapp.xsdk.ru/` возвращает "OK"
- [ ] ✅ Swagger UI загружается
- [ ] ✅ Тесты API проходят

---

## 🆘 Нужна помощь?

**Отправьте мне:**
1. Вывод команды: `php test-config.php`
2. Логи ошибок: `tail -50 /home/c/cv82602/logs/error.log`
3. Результат: `mysql -u cv82602_trade -p -e "SHOW TABLES;" cv82602_trade`
