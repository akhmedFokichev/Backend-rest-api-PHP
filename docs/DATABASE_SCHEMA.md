# Структура базы данных

## 📊 Схема БД

```
┌─────────────────────────────────────────────────────────────────┐
│                          DATABASE SCHEMA                        │
│                     cv82602_slimdev (MySQL)                     │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────────────────┐
│     identity_users (👤)      │  Модуль: Identity
├──────────────────────────────┤
│ PK  id                  INT  │
│ UK  login           VARCHAR  │  ← уникальный логин
│     pass_hash       VARCHAR  │  ← bcrypt hash
│     access_level    TINYINT  │  ← 0=user, 1=admin
│     created_at      DATETIME │
│     updated_at      DATETIME │
└──────────────────┬───────────┘
                   │ 1
                   │
                   │ N
┌──────────────────┴───────────┐
│    identity_session (🔑)     │  Модуль: Identity
├──────────────────────────────┤
│ PK  id                  INT  │
│ FK  user_id             INT  │  → identity_users.id
│     access_token    VARCHAR  │  ← JWT access token
│     refresh_token   VARCHAR  │  ← JWT refresh token
│     expiresIn           INT  │  ← TTL в секундах
│     client_id       VARCHAR  │  ← web_app, mobile_app
│     secret_key      VARCHAR  │  ← ключ для подписи JWT
│     created_at      DATETIME │
│     updated_at      DATETIME │
└──────────────────────────────┘


┌──────────────────────────────┐
│      ref_country (🌍)        │  Модуль: Reference
├──────────────────────────────┤
│ PK  id                  INT  │
│ UK  uuid            VARCHAR  │
│     parent_uuid     VARCHAR  │  ← для иерархии (self-ref)
│     is_catalog      TINYINT  │  ← 0=элемент, 1=папка
│     code            VARCHAR  │  ← ISO code (RU, US)
│     name            VARCHAR  │  ← название
│     sort_order          INT  │  ← порядок сортировки
│     created_at      DATETIME │
│     updated_at      DATETIME │
└──────────────────────────────┘


┌──────────────────────────────┐
│    storage_files (📁)        │  Модуль: Storage
├──────────────────────────────┤
│ PK  id                  INT  │
│ UK  uuid            VARCHAR  │
│     original_name   VARCHAR  │  ← оригинальное имя
│     storage_name    VARCHAR  │  ← UUID.ext
│     mime_type       VARCHAR  │  ← MIME тип
│     size                INT  │  ← размер в байтах
│     path            VARCHAR  │  ← относительный путь
│     created_at      DATETIME │
│     updated_at      DATETIME │
└──────────────────────────────┘
```

---

## 📋 Детальное описание таблиц

### 1. identity_users

**Назначение:** Хранение пользователей системы

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | INT (PK) | Автоинкремент ID |
| `login` | VARCHAR(100) UNIQUE | Логин (email или username) |
| `pass_hash` | VARCHAR(255) | Bcrypt хеш пароля |
| `access_level` | TINYINT | 0=user, 1=admin, 2=superadmin |
| `created_at` | DATETIME | Дата регистрации |
| `updated_at` | DATETIME | Дата последнего обновления |

**Индексы:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`login`)
- INDEX (`login`)

**Пример данных:**
```sql
id=1, login='user@example.com', pass_hash='$2y$10$...', access_level=0
```

---

### 2. identity_session

**Назначение:** Хранение JWT токенов и сессий пользователей

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | INT (PK) | Автоинкремент ID |
| `user_id` | INT (FK) | ID пользователя → `identity_users.id` |
| `access_token` | VARCHAR(500) | JWT access токен |
| `refresh_token` | VARCHAR(500) | JWT refresh токен |
| `expiresIn` | INT | Время жизни токена (секунды) |
| `client_id` | VARCHAR(100) | Клиент (web_app, mobile_app) |
| `secret_key` | VARCHAR(255) | Секретный ключ для JWT |
| `created_at` | DATETIME | Дата создания сессии |
| `updated_at` | DATETIME | Дата последнего refresh |

**Индексы:**
- PRIMARY KEY (`id`)
- INDEX (`user_id`)
- INDEX (`access_token`)
- INDEX (`refresh_token`)

**Внешние ключи:**
- `user_id` → `identity_users.id` (CASCADE)

**Пример данных:**
```sql
id=1, user_id=1, access_token='eyJ0eXAi...', refresh_token='eyJ0eXAi...', 
expiresIn=3600, client_id='web_app'
```

---

### 3. ref_country

**Назначение:** Справочник стран (поддерживает иерархическую структуру)

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | INT (PK) | Автоинкремент ID |
| `uuid` | VARCHAR(36) UNIQUE | UUID для API |
| `parent_uuid` | VARCHAR(36) NULL | UUID родителя (для иерархии) |
| `is_catalog` | TINYINT | 0=элемент, 1=папка/каталог |
| `code` | VARCHAR(10) | ISO код (RU, US, GB) |
| `name` | VARCHAR(255) | Название страны |
| `sort_order` | INT | Порядок сортировки |
| `created_at` | DATETIME | Дата создания |
| `updated_at` | DATETIME | Дата обновления |

**Индексы:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`uuid`)
- INDEX (`uuid`)
- INDEX (`parent_uuid`)
- INDEX (`code`)
- INDEX (`sort_order`)

**Иерархическая структура:**
```
Каталог: Европа (is_catalog=1)
  ├─ Россия (parent_uuid → Европа)
  ├─ Германия
  └─ Франция
Каталог: Азия (is_catalog=1)
  ├─ Китай (parent_uuid → Азия)
  └─ Япония
```

**Пример данных:**
```sql
uuid='550e8400-...', parent_uuid=NULL, is_catalog=0, code='RU', 
name='Россия', sort_order=1
```

---

### 4. storage_files

**Назначение:** Метаданные загруженных файлов

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | INT (PK) | Автоинкремент ID |
| `uuid` | VARCHAR(36) UNIQUE | UUID файла для API |
| `original_name` | VARCHAR(255) | Оригинальное имя файла |
| `storage_name` | VARCHAR(255) | Имя в хранилище (UUID.ext) |
| `mime_type` | VARCHAR(100) | MIME тип (image/png, application/pdf) |
| `size` | INT | Размер в байтах |
| `path` | VARCHAR(500) | Относительный путь к файлу |
| `created_at` | DATETIME | Дата загрузки |
| `updated_at` | DATETIME | Дата обновления |

**Индексы:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`uuid`)
- INDEX (`uuid`)
- INDEX (`created_at`)

**Пример данных:**
```sql
uuid='550e8400-...', original_name='document.pdf', 
storage_name='550e8400-....pdf', mime_type='application/pdf', 
size=1024000, path='550e8400-....pdf'
```

---

## 🔗 Связи между таблицами

```
identity_users (1) ──────< (N) identity_session
    │
    └─ Один пользователь может иметь несколько активных сессий
       (например: веб + мобильное приложение)
```

Остальные таблицы независимы (no relations), что соответствует **модульной архитектуре**.

---

## 📈 Статистика и объемы

### Примерные объемы данных

| Таблица | Строк | Размер строки | Объем (1000 записей) |
|---------|-------|---------------|---------------------|
| identity_users | ~1000 | ~500 bytes | ~500 KB |
| identity_session | ~5000 | ~1.5 KB | ~7.5 MB |
| ref_country | ~250 | ~300 bytes | ~75 KB |
| storage_files | ~10000 | ~500 bytes | ~5 MB (+ физические файлы) |

### Индексы

Общее количество индексов: **14**

- `identity_users`: 2 индекса
- `identity_session`: 4 индекса
- `ref_country`: 5 индексов
- `storage_files`: 3 индекса

---

## 🔒 Безопасность данных

### 1. Пароли
- ✅ Хранятся в виде bcrypt-хешей
- ✅ Никогда не возвращаются в API
- ✅ Минимальная длина: 6 символов

### 2. JWT токены
- ✅ Хранятся в БД для валидации
- ✅ Подписываются secret_key
- ✅ Имеют TTL (expiresIn)

### 3. UUID вместо ID
- ✅ Внешние API используют UUID (не автоинкремент ID)
- ✅ Затрудняет перебор записей

### 4. Файлы
- ✅ Физически хранятся вне public_html
- ✅ Доступ только через API
- ✅ UUID-именование скрывает оригинальные имена

---

## 🛠️ Миграции и версионирование

### Текущая версия: 1.0

**Файлы миграций:**
- `sql/install_all.sql` - полная установка
- `sql/identity/*.sql` - Identity модуль
- `sql/reference/*.sql` - Reference модуль
- `sql/storage/*.sql` - Storage модуль

### Rollback (откат)

```sql
DROP TABLE IF EXISTS `identity_session`;
DROP TABLE IF EXISTS `identity_users`;
DROP TABLE IF EXISTS `ref_country`;
DROP TABLE IF EXISTS `storage_files`;
```

---

## 📊 ERD диаграмма (Entity-Relationship Diagram)

```
┌─────────────────┐
│ identity_users  │
│   (Пользователи)│
├─────────────────┤
│ • id            │
│ • login         │
│ • pass_hash     │
│ • access_level  │
└────────┬────────┘
         │ 1
         │
         │ N
         ▼
┌─────────────────┐
│identity_session │
│   (JWT Сессии)  │
├─────────────────┤
│ • id            │
│ • user_id   [FK]│
│ • access_token  │
│ • refresh_token │
│ • expiresIn     │
└─────────────────┘


┌─────────────────┐
│   ref_country   │
│    (Страны)     │
├─────────────────┤
│ • id            │
│ • uuid          │
│ • parent_uuid   │← self-reference
│ • code          │
│ • name          │
└─────────────────┘


┌─────────────────┐
│ storage_files   │
│    (Файлы)      │
├─────────────────┤
│ • id            │
│ • uuid          │
│ • original_name │
│ • storage_name  │
│ • mime_type     │
│ • size          │
└─────────────────┘
```

---

## 🎯 Best Practices

1. ✅ Все таблицы используют `utf8mb4` (emoji support)
2. ✅ Все таблицы используют `InnoDB` (транзакции, FK)
3. ✅ Временные поля: `created_at`, `updated_at`
4. ✅ UUID для внешних API (защита от enumeration)
5. ✅ Индексы на часто используемых полях
6. ✅ Внешние ключи с CASCADE (автоматическое удаление)
7. ✅ `IF NOT EXISTS` для безопасного повторного запуска

---

## 📝 Changelog

### Version 1.0 (Initial Release)
- ✅ Модуль Identity: `identity_users`, `identity_session`
- ✅ Модуль Reference: `ref_country`
- ✅ Модуль Storage: `storage_files`
