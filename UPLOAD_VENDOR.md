# Инструкция по загрузке vendor на сервер без Composer

## Шаг 1: Установка зависимостей локально

На вашей локальной машине (где есть Composer) выполните:

```bash
cd /Users/azapsh/.cursor/worktrees/Backend-rest-api-PHP/bjr
composer install --no-dev --optimize-autoloader
```

Это установит все зависимости, включая `zircote/swagger-php`.

## Шаг 2: Проверка установки

Убедитесь, что библиотека установлена:

```bash
ls -la vendor/zircote/swagger-php
```

Должна существовать директория с файлами библиотеки.

## Шаг 3: Загрузка на сервер

### Вариант A: Загрузка через FTP/SFTP клиент

1. Подключитесь к серверу через FTP/SFTP клиент (FileZilla, Cyberduck, WinSCP и т.д.)

2. Загрузите **всю директорию** `vendor` на сервер:
   - Локальный путь: `/Users/azapsh/.cursor/worktrees/Backend-rest-api-PHP/bjr/vendor`
   - Серверный путь: `/home/c/cv82602/tradeApp/vendor`

3. Убедитесь, что загружена структура:
   ```
   /home/c/cv82602/tradeApp/vendor/
   ├── autoload.php
   ├── composer/
   ├── zircote/
   │   └── swagger-php/
   ├── nikic/
   │   └── fast-route/
   ├── slim/
   └── ... (другие зависимости)
   ```

### Вариант B: Загрузка через командную строку (если есть SSH доступ)

Если у вас есть SSH доступ к серверу, можно использовать `scp` или `rsync`:

```bash
# Используя scp
scp -r vendor/ user@server:/home/c/cv82602/tradeApp/

# Или используя rsync (более эффективно)
rsync -avz --delete vendor/ user@server:/home/c/cv82602/tradeApp/vendor/
```

**Важно:** Замените `user@server` на ваши реальные данные для подключения к серверу.

## Шаг 4: Проверка прав доступа

После загрузки убедитесь, что веб-сервер имеет доступ к файлам:

```bash
# На сервере (если есть SSH доступ)
chmod -R 755 /home/c/cv82602/tradeApp/vendor
```

Или через FTP/SFTP клиент установите права:
- Папки: 755
- Файлы: 644

## Шаг 5: Проверка работы

После загрузки проверьте:

1. **Проверьте наличие библиотеки на сервере:**
   ```bash
   ls -la /home/c/cv82602/tradeApp/vendor/zircote/swagger-php
   ```

2. **Откройте в браузере:**
   - `https://tradeapp.xsdk.ru/api-docs.json` — должен вернуть JSON
   - `https://tradeapp.xsdk.ru/swagger-ui.html` — должна загрузиться Swagger UI

## Альтернативный вариант: Загрузка только недостающей библиотеки

Если на сервере уже есть часть зависимостей, можно загрузить только `zircote/swagger-php`:

1. Локально установите только эту библиотеку:
   ```bash
   composer require zircote/swagger-php --no-dev
   ```

2. Загрузите только директорию `vendor/zircote` на сервер

3. Обновите `vendor/autoload.php` на сервере (или загрузите его заново)

## Примечания

- Размер директории `vendor` может быть большим (десятки мегабайт). Убедитесь, что у вас достаточно места на сервере.
- Если загрузка прерывается, можно загружать по частям или использовать архивацию:
  ```bash
  # Локально создайте архив
  tar -czf vendor.tar.gz vendor/
  
  # Загрузите архив на сервер и распакуйте
  tar -xzf vendor.tar.gz
  ```
