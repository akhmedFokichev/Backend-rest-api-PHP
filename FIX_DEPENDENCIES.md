# Исправление ошибки зависимостей

## Проблема
Ошибка: `Interface "FastRoute\RouteParser" not found`

Это означает, что на сервере не установлены или установлены не полностью зависимости Composer.

## Решение

### 1. Подключитесь к серверу по SSH

### 2. Перейдите в директорию проекта
```bash
cd /home/c/cv82602/tradeApp
```

### 3. Убедитесь, что Composer установлен
```bash
composer --version
```

Если Composer не установлен, установите его согласно инструкциям вашего хостинга.

### 4. Установите/обновите зависимости
```bash
composer install --no-dev --optimize-autoloader
```

Или если нужно обновить зависимости:
```bash
composer update --no-dev --optimize-autoloader
```

**ВАЖНО:** Убедитесь, что установлена библиотека `zircote/swagger-php`:
```bash
composer require zircote/swagger-php
```

### 5. Проверьте, что зависимости установлены
Убедитесь, что существуют необходимые директории:
```bash
# Проверка fast-route (для Slim)
ls -la vendor/nikic/fast-route

# Проверка swagger-php (для документации)
ls -la vendor/zircote/swagger-php
```

Если какой-то из пакетов отсутствует, установите его:
```bash
composer require nikic/fast-route
composer require zircote/swagger-php
```

### 6. Проверьте автозагрузку
Убедитесь, что файл `vendor/autoload.php` существует и доступен:
```bash
ls -la vendor/autoload.php
```

### 7. Проверьте права доступа
Убедитесь, что веб-сервер имеет доступ к директории `vendor`:
```bash
chmod -R 755 vendor
```

## Альтернативное решение (если Composer недоступен)

Если Composer недоступен на сервере, можно установить зависимости локально и загрузить директорию `vendor` на сервер:

1. На локальной машине выполните:
```bash
composer install --no-dev --optimize-autoloader
```

2. Загрузите директорию `vendor` на сервер через FTP/SFTP

## Проверка после исправления

После установки зависимостей проверьте:

1. Откройте в браузере: `https://tradeapp.xsdk.ru/`
   - Должно вернуться "OK"

2. Откройте: `https://tradeapp.xsdk.ru/api-docs.json`
   - Должен вернуться JSON со спецификацией OpenAPI

3. Откройте: `https://tradeapp.xsdk.ru/swagger-ui.html`
   - Должна загрузиться страница Swagger UI с документацией API

## Дополнительная информация

Если проблема сохраняется, проверьте логи ошибок:
```bash
tail -f /path/to/error.log
```

Или проверьте логи Apache:
```bash
tail -f /var/log/apache2/error.log
```
