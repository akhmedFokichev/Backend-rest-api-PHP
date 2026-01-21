# Настройка Swagger/OpenAPI документации

## Быстрый старт

### 1. Установка зависимостей

На хостинге выполните:
```bash
composer require zircote/swagger-php
```

Или если устанавливаете все зависимости:
```bash
composer install
```

### 2. Проверка работы

После установки зависимостей и запуска сервера:

1. **Откройте Swagger UI:**
   ```
   http://your-domain.com/swagger-ui.html
   ```

2. **Проверьте JSON спецификацию:**
   ```
   http://your-domain.com/api-docs.json
   ```

### 3. Что было добавлено

✅ Установлена библиотека `zircote/swagger-php` в `composer.json`  
✅ Создан базовый класс `src/Http/OpenApi.php` с информацией об API  
✅ Добавлены аннотации к `IdentityController` (регистрация, логин, refresh)  
✅ Добавлены аннотации к `CountryController` (CRUD операции)  
✅ Создан `DocsController` для генерации OpenAPI JSON  
✅ Добавлены роуты `/api-docs.json` и `/swagger-ui.html`  
✅ Создана HTML страница Swagger UI  

### 4. Настройка для продакшена

Обновите URL сервера в файле `src/Http/OpenApi.php`:
```php
#[OA\Server(
    url: "https://your-production-domain.com",
    description: "Production server"
)]
```

## Дополнительная информация

Подробная документация находится в файле `docs/API_DOCUMENTATION.md`
