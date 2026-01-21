# API Documentation (Swagger/OpenAPI)

## Установка

1. Установите зависимости через Composer:
```bash
composer install
```

Или если нужно добавить только swagger-php:
```bash
composer require zircote/swagger-php
```

## Использование

### Просмотр документации

После установки зависимостей и запуска сервера, документация будет доступна по следующим адресам:

1. **Swagger UI** (интерактивная документация):
   ```
   http://your-domain.com/swagger-ui.html
   ```

2. **OpenAPI JSON** (спецификация в формате JSON):
   ```
   http://your-domain.com/api-docs.json
   ```

### Генерация документации

Документация генерируется автоматически из аннотаций в коде при обращении к `/api-docs.json`.

Аннотации находятся в:
- `src/Http/OpenApi.php` - базовая информация об API
- `src/Http/Controller/IdentityController.php` - эндпоинты аутентификации
- `src/Http/Controller/Reference/CountryController.php` - эндпоинты справочников

### Структура файлов

```
src/
├── Http/
│   ├── OpenApi.php                    # Базовая конфигурация OpenAPI
│   ├── Controller/
│   │   ├── DocsController.php        # Контроллер для генерации JSON
│   │   ├── IdentityController.php    # Контроллер с аннотациями
│   │   └── Reference/
│   │       └── CountryController.php # Контроллер с аннотациями
│   └── routes.php                     # Роуты включая /api-docs.json

public_html/
└── swagger-ui.html                    # HTML страница Swagger UI
```

## Добавление новых эндпоинтов в документацию

Для добавления документации к новому эндпоинту используйте аннотации OpenAPI:

```php
use OpenApi\Attributes as OA;

#[OA\Get(
    path: "/your/endpoint",
    summary: "Краткое описание",
    description: "Подробное описание",
    tags: ["YourTag"],
    responses: [
        new OA\Response(
            response: 200,
            description: "Успешный ответ",
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(...)
            )
        )
    ]
)]
public function yourMethod(...) {
    // ...
}
```

## Настройка для продакшена

1. Обновите URL сервера в `src/Http/OpenApi.php`:
```php
#[OA\Server(
    url: "https://your-production-domain.com",
    description: "Production server"
)]
```

2. При необходимости ограничьте доступ к документации (добавьте авторизацию в роуты).

## Полезные ссылки

- [OpenAPI Specification](https://swagger.io/specification/)
- [Swagger PHP Documentation](https://zircote.github.io/swagger-php/)
- [Swagger UI](https://swagger.io/tools/swagger-ui/)
