# 🗄️ Установка базы данных

## ✅ Что создано

SQL миграции для **всех 3 модулей**:

```
sql/
├── 📄 install_all.sql           ← ГЛАВНЫЙ ФАЙЛ (установка всех модулей)
├── 📄 README.md                 ← документация
│
├── identity/                    🔐 Модуль Identity
│   ├── install.sql              ← установка модуля Identity
│   ├── identity_users.sql       ← таблица пользователей
│   └── identity_session.sql     ← таблица сессий (JWT)
│
├── reference/                   📚 Модуль Reference
│   └── ref_country.sql          ← справочник стран + тестовые данные
│
└── storage/                     💾 Модуль Storage
    └── storage_files.sql        ← таблица файлов
```

---

## 🚀 Быстрая установка (РЕКОМЕНДУЕТСЯ)

### Способ 1: Через командную строку SSH

```bash
# Подключитесь к серверу по SSH
ssh cv82602@tradeapp.xsdk.ru

# Перейдите в директорию проекта
cd /home/c/cv82602/tradeApp

# Выполните установку всех модулей одной командой
mysql -u cv82602_slimdev -p cv82602_slimdev < sql/install_all.sql
```

После ввода пароля создадутся **все 4 таблицы** + **тестовые данные** для справочника стран.

---

### Способ 2: Через phpMyAdmin

1. Откройте phpMyAdmin на хостинге
2. Выберите базу данных `cv82602_slimdev`
3. Перейдите на вкладку **SQL**
4. Откройте файл `sql/install_all.sql` в текстовом редакторе
5. Скопируйте **все содержимое** файла
6. Вставьте в текстовое поле phpMyAdmin
7. Нажмите кнопку **Выполнить** (Go)

Результат: `4 queries successfully executed`

---

## 📊 Созданные таблицы

После установки будут созданы:

| Таблица | Модуль | Назначение |
|---------|--------|------------|
| `identity_users` | Identity 🔐 | Пользователи системы |
| `identity_session` | Identity 🔐 | JWT токены и сессии |
| `ref_country` | Reference 📚 | Справочник стран |
| `storage_files` | Storage 💾 | Метаданные файлов |

---

## ✅ Проверка установки

### Проверка через SQL

```sql
-- Показать все таблицы
SHOW TABLES;

-- Должны быть:
-- +---------------------------+
-- | Tables_in_cv82602_slimdev |
-- +---------------------------+
-- | identity_session          |
-- | identity_users            |
-- | ref_country               |
-- | storage_files             |
-- +---------------------------+

-- Проверка структуры
DESCRIBE identity_users;
DESCRIBE identity_session;
DESCRIBE ref_country;
DESCRIBE storage_files;

-- Проверка тестовых данных (8 стран)
SELECT uuid, code, name FROM ref_country ORDER BY sort_order;
```

### Проверка через API

После установки таблиц проверьте API:

**1. Swagger UI:**
```
https://tradeapp.xsdk.ru/swagger-ui.html
```

**2. Регистрация пользователя:**
```bash
curl -X POST https://tradeapp.xsdk.ru/identity/registration \
  -H "Content-Type: application/json" \
  -d '{"login": "test@example.com", "password": "123456"}'
```

**3. Список стран:**
```bash
curl https://tradeapp.xsdk.ru/reference/country
```

---

## 🔄 Установка модулей по отдельности

Если нужно установить только определенные модули:

### Только Identity
```bash
mysql -u cv82602_slimdev -p cv82602_slimdev < sql/identity/install.sql
```

### Только Reference
```bash
mysql -u cv82602_slimdev -p cv82602_slimdev < sql/reference/ref_country.sql
```

### Только Storage
```bash
mysql -u cv82602_slimdev -p cv82602_slimdev < sql/storage/storage_files.sql
```

---

## 🗑️ Удаление таблиц (для переустановки)

**⚠️ ВНИМАНИЕ: Это удалит все данные!**

```sql
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `identity_session`;
DROP TABLE IF EXISTS `identity_users`;
DROP TABLE IF EXISTS `ref_country`;
DROP TABLE IF EXISTS `storage_files`;

SET FOREIGN_KEY_CHECKS = 1;
```

После удаления можно заново выполнить `sql/install_all.sql`.

---

## 📝 Структура таблиц

### 1. identity_users (Пользователи)

```sql
CREATE TABLE `identity_users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `login` VARCHAR(100) UNIQUE,           -- email или username
    `pass_hash` VARCHAR(255),              -- bcrypt hash
    `access_level` TINYINT UNSIGNED,       -- 0=user, 1=admin
    `created_at` DATETIME,
    `updated_at` DATETIME
);
```

### 2. identity_session (JWT сессии)

```sql
CREATE TABLE `identity_session` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED,                -- FK → identity_users.id
    `access_token` VARCHAR(500),
    `refresh_token` VARCHAR(500),
    `expiresIn` INT UNSIGNED,
    `client_id` VARCHAR(100),
    `secret_key` VARCHAR(255),
    `created_at` DATETIME,
    `updated_at` DATETIME,
    FOREIGN KEY (`user_id`) REFERENCES `identity_users`(`id`) ON DELETE CASCADE
);
```

### 3. ref_country (Справочник стран)

```sql
CREATE TABLE `ref_country` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `uuid` VARCHAR(36) UNIQUE,
    `parent_uuid` VARCHAR(36),             -- для иерархии
    `is_catalog` TINYINT(1),               -- 0=элемент, 1=папка
    `code` VARCHAR(10),                    -- ISO: RU, US, GB
    `name` VARCHAR(255),
    `sort_order` INT,
    `created_at` DATETIME,
    `updated_at` DATETIME
);
```

### 4. storage_files (Файлы)

```sql
CREATE TABLE `storage_files` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `uuid` VARCHAR(36) UNIQUE,
    `original_name` VARCHAR(255),
    `storage_name` VARCHAR(255),           -- UUID.ext
    `mime_type` VARCHAR(100),
    `size` INT UNSIGNED,
    `path` VARCHAR(500),
    `created_at` DATETIME,
    `updated_at` DATETIME
);
```

---

## 🎯 Что дальше

После установки БД:

1. ✅ Загрузите обновленный код на сервер (если еще не загружен)
2. ✅ Проверьте Swagger UI: `https://tradeapp.xsdk.ru/swagger-ui.html`
3. ✅ Создайте тестового пользователя через `/identity/registration`
4. ✅ Залогиньтесь через `/identity/login` и получите JWT токены
5. ✅ Проверьте список стран через `/reference/country`
6. ✅ Загрузите тестовый файл через `/storage/files`

---

## 🔧 Устранение проблем

### Ошибка: Table already exists

Если таблицы уже существуют, но нужно их пересоздать:

```sql
-- Удалите старые таблицы (см. раздел "Удаление таблиц")
-- Затем заново выполните install_all.sql
```

### Ошибка: Access denied

Проверьте:
- Правильность имени пользователя БД (`cv82602_slimdev`)
- Правильность имени базы данных (`cv82602_slimdev`)
- Правильность пароля

### Ошибка: Cannot add foreign key constraint

Это означает, что пытаетесь создать `identity_session` до `identity_users`.
Решение: используйте `sql/install_all.sql` (правильный порядок).

---

## 📚 Документация

Детальная документация:
- `sql/README.md` - инструкции по установке
- `docs/DATABASE_SCHEMA.md` - схема БД и ERD диаграммы
- `docs/STORAGE_MODULE.md` - документация модуля Storage
- `docs/STORAGE_ARCHITECTURE.md` - архитектура модулей

---

## ✨ Итого

После установки у вас будет:

- ✅ **4 таблицы** для всех модулей
- ✅ **8 стран** в справочнике (тестовые данные)
- ✅ Готовая структура для работы API
- ✅ Связи между таблицами (Foreign Keys)
- ✅ Индексы для оптимизации запросов

**Всё готово к работе!** 🎉
