# Admin Frontend (AdminLTE 3 + PHP + JS)

Админ-панель для **Backend-rest-api-PHP** (Slim). Без PDO — все данные через REST API.

## Расположение в проекте

```
Backend-rest-api-PHP/
├── public_html/          ← document root (лендинг + /api + /admin)
├── api/                  ← Slim (routes.php, src/, bootstrap.php)
├── admin/                ← эта админка
└── config/
```

## Локальный запуск

```bash
cd public_html
php -S localhost:8080
```

- Лендинг: http://localhost:8080/
- API: http://localhost:8080/api/v1/db-check
- Админка: http://localhost:8080/admin/login

Вход — учётная запись из вашей БД (`POST /api/v1/user/login`).

## Конфигурация API

`app/Config/api.php`:

```php
return [
    'base_url' => 'http://localhost:8080/api/v1',
    'mock_enabled' => false,  // true — демо без Slim
    'timeout' => 15,
];
```

На продакшене укажите реальный URL API, например `https://your-domain.com/api/v1`.

## Интеграция с Slim

| Действие | Slim endpoint | Админка |
|----------|---------------|---------|
| Вход | `POST /api/v1/user/login` | `/admin/login` |
| Выход | `POST /api/v1/user/logout` | `/admin/logout` |
| Список пользователей | `GET /api/v1/user/list` | `/admin/users` (Moderator+) |
| Удаление | `DELETE /api/v1/user/{id}` | кнопка (Admin) |

Ответ логина: `accessToken` → сохраняется в PHP-сессии.

JS вызывает **same-origin proxy**: `/api/proxy/user/list` → PHP добавляет `Authorization: Bearer` → Slim.

## Роли и меню

Числовые роли Slim (`Role` enum) маппятся в права UI:

| Роль | value | Меню |
|------|-------|------|
| Admin | 100 | всё |
| Moderator | 50 | пользователи |
| User | 10 | только дашборд |

## Деплой

- Document root → `public_html/`
- API: `/api/v1/*`, админка: `/admin/*` на одном домене
- `api.php` определяет URL API автоматически по `HTTP_HOST`

## Mock-режим

`mock_enabled => true` — работа без запущенного Slim (демо-логин `admin@example.com` / `admin`).

## Требования

- PHP 8.1+
- Расширение `curl` (при `mock_enabled = false`)
