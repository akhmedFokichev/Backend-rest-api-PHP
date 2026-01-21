# Деплой модуля Storage на сервер

## Что было создано

### ✅ Новый модуль Storage с полной архитектурой:

```
src/
├── Domain/Storage/
│   ├── Entity/File.php
│   └── Repository/FileRepositoryInterface.php
├── Application/Storage/
│   ├── UploadFileUseCase.php
│   ├── ListFilesUseCase.php
│   ├── GetFileUseCase.php
│   └── DeleteFileUseCase.php
├── Infrastructure/Storage/
│   └── FileRepositoryMysql.php
└── Http/Controller/Storage/
    └── FileController.php
```

### ✅ Обновленные файлы:
- `src/Http/routes.php` - добавлены роуты для Storage
- `src/Http/OpenApi.php` - добавлен тег Storage
- `public_html/index.php` - интеграция Storage модуля

### ✅ SQL миграция:
- `sql/storage/storage_files.sql`

### ✅ Директория хранения:
- `storage/files/` - для загруженных файлов

---

## Шаги для деплоя

### Шаг 1: Создайте таблицу в БД

Выполните на сервере SQL миграцию:

```sql
CREATE TABLE IF NOT EXISTS `storage_files` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` VARCHAR(36) NOT NULL UNIQUE COMMENT 'UUID файла',
    `original_name` VARCHAR(255) NOT NULL COMMENT 'Оригинальное имя файла',
    `storage_name` VARCHAR(255) NOT NULL COMMENT 'Имя файла в хранилище',
    `mime_type` VARCHAR(100) NOT NULL COMMENT 'MIME тип файла',
    `size` INT UNSIGNED NOT NULL COMMENT 'Размер файла в байтах',
    `path` VARCHAR(500) NOT NULL COMMENT 'Относительный путь к файлу',
    `created_at` DATETIME NOT NULL COMMENT 'Дата создания',
    `updated_at` DATETIME NOT NULL COMMENT 'Дата обновления',
    INDEX `idx_uuid` (`uuid`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Шаг 2: Загрузите файлы на сервер

Через FTP/SFTP загрузите:

**Новые файлы:**
- `src/Domain/Storage/` (вся директория)
- `src/Application/Storage/` (вся директория)
- `src/Infrastructure/Storage/` (вся директория)
- `src/Http/Controller/Storage/` (вся директория)
- `sql/storage/` (SQL миграция)

**Обновленные файлы:**
- `src/Http/routes.php`
- `src/Http/OpenApi.php`
- `public_html/index.php`

### Шаг 3: Создайте директорию storage на сервере

На сервере создайте директорию и установите права:

```bash
cd /home/c/cv82602/tradeApp
mkdir -p storage/files
chmod -R 755 storage
chown -R www-data:www-data storage  # или пользователь веб-сервера
```

Или через FTP/SFTP:
1. Создайте директорию `storage/files/`
2. Установите права: 755

### Шаг 4: Проверьте работу

**1. Проверьте Swagger UI:**
```
https://tradeapp.xsdk.ru/swagger-ui.html
```

Должна появиться новая секция **Storage** с 4 эндпоинтами.

**2. Протестируйте загрузку файла:**
```bash
curl -X POST https://tradeapp.xsdk.ru/storage/files \
  -F "file=@test.txt"
```

**3. Получите список файлов:**
```bash
curl https://tradeapp.xsdk.ru/storage/files
```

**4. Скачайте файл (замените UUID на полученный):**
```bash
curl -O https://tradeapp.xsdk.ru/storage/files/{uuid}
```

**5. Удалите файл:**
```bash
curl -X DELETE https://tradeapp.xsdk.ru/storage/files/{uuid}
```

---

## API Endpoints

### POST /storage/files
Загрузка файла (multipart/form-data)

### GET /storage/files
Получение списка всех файлов

### GET /storage/files/{uuid}
Скачивание файла по UUID

### DELETE /storage/files/{uuid}
Удаление файла

---

## Проверка конфигурации PHP

Убедитесь, что в `php.ini` разрешена загрузка файлов:

```ini
file_uploads = On
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
```

---

## Что дальше

После деплоя можно добавить:

1. **Авторизацию** - только авторизованные пользователи могут загружать
2. **Валидацию** - ограничение типов и размеров файлов
3. **Thumbnails** - генерация превью для изображений
4. **Категории** - организация файлов по папкам
5. **Временные ссылки** - одноразовые ссылки для скачивания
