# Установка библиотеки swagger-php

## Проблема
`composer.lock` не синхронизирован с `composer.json`. Библиотека `zircote/swagger-php` добавлена в `composer.json`, но отсутствует в `composer.lock`.

## Решение

### Шаг 1: Обновите зависимости локально

На вашей локальной машине выполните:

```bash
cd /Users/azapsh/.cursor/worktrees/Backend-rest-api-PHP/bjr
composer update --no-dev --optimize-autoloader
```

Или если хотите обновить только swagger-php:

```bash
composer require zircote/swagger-php --no-dev --optimize-autoloader
```

Это:
1. Добавит `zircote/swagger-php` в `composer.lock`
2. Установит библиотеку в `vendor/zircote/swagger-php`
3. Обновит автозагрузку

### Шаг 2: Проверьте установку

Убедитесь, что библиотека установлена:

```bash
ls -la vendor/zircote/swagger-php
```

Должна существовать директория с файлами библиотеки.

### Шаг 3: Загрузите на сервер

После успешной установки локально, загрузите на сервер:

#### Вариант A: Загрузите всю директорию vendor

Через FTP/SFTP клиент загрузите:
- **Локальный путь:** `/Users/azapsh/.cursor/worktrees/Backend-rest-api-PHP/bjr/vendor`
- **Серверный путь:** `/home/c/cv82602/tradeApp/vendor`

#### Вариант B: Загрузите только недостающие части

Если на сервере уже есть часть зависимостей, можно загрузить только:
1. `vendor/zircote/` - директория с библиотекой swagger-php
2. `vendor/composer/` - обновленные файлы автозагрузки
3. `vendor/autoload.php` - обновленный файл автозагрузки

### Шаг 4: Загрузите обновленные файлы

Также загрузите на сервер обновленные файлы:
- `composer.json` (уже должен быть актуальным)
- `composer.lock` (будет обновлен после `composer update`)

### Шаг 5: Проверка на сервере

После загрузки проверьте:

1. **Проверьте наличие библиотеки:**
   ```bash
   ls -la /home/c/cv82602/tradeApp/vendor/zircote/swagger-php
   ```

2. **Проверьте в браузере:**
   - `https://tradeapp.xsdk.ru/api-docs.json` — должен вернуть JSON
   - `https://tradeapp.xsdk.ru/swagger-ui.html` — должна загрузиться Swagger UI

## Альтернатива: Использование архива

Если директория vendor большая, можно создать архив:

```bash
# Локально создайте архив
cd /Users/azapsh/.cursor/worktrees/Backend-rest-api-PHP/bjr
tar -czf vendor.tar.gz vendor/

# Загрузите vendor.tar.gz на сервер
# На сервере распакуйте:
# cd /home/c/cv82602/tradeApp
# tar -xzf vendor.tar.gz
```

## Примечания

- После выполнения `composer update` файл `composer.lock` будет обновлен
- Убедитесь, что загружаете обновленный `composer.lock` на сервер
- Если на сервере уже есть часть зависимостей, можно загрузить только недостающие части
