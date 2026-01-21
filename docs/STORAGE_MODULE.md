# Storage Module (Модуль хранения файлов)

## Описание

Модуль Storage предназначен для работы с файлами через REST API:
- Загрузка файлов
- Получение списка файлов
- Скачивание файлов
- Удаление файлов

## Архитектура модуля

### Domain слой
```
src/Domain/Storage/
├── Entity/
│   └── File.php                         # Сущность файла
└── Repository/
    └── FileRepositoryInterface.php      # Интерфейс репозитория
```

**File Entity:**
- `uuid` - уникальный идентификатор
- `originalName` - оригинальное имя файла
- `storageName` - имя в хранилище (UUID + расширение)
- `mimeType` - MIME тип файла
- `size` - размер в байтах
- `path` - относительный путь

### Application слой
```
src/Application/Storage/
├── UploadFileUseCase.php     # Загрузка файла
├── ListFilesUseCase.php      # Список файлов
├── GetFileUseCase.php        # Получение файла
└── DeleteFileUseCase.php     # Удаление файла
```

### Infrastructure слой
```
src/Infrastructure/Storage/
└── FileRepositoryMysql.php   # Реализация репозитория для MySQL
```

### Http слой
```
src/Http/Controller/Storage/
└── FileController.php        # HTTP контроллер
```

## API Endpoints

### 1. Загрузка файла
```
POST /storage/files
Content-Type: multipart/form-data

Body: file (binary)
```

**Ответ (201):**
```json
{
  "id": 1,
  "uuid": "550e8400-e29b-41d4-a716-446655440000",
  "originalName": "document.pdf",
  "mimeType": "application/pdf",
  "size": 1024000,
  "createdAt": "2024-01-20 12:00:00"
}
```

### 2. Получение списка файлов
```
GET /storage/files
```

**Ответ (200):**
```json
[
  {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "originalName": "document.pdf",
    "mimeType": "application/pdf",
    "size": 1024000,
    "createdAt": "2024-01-20 12:00:00"
  }
]
```

### 3. Скачивание файла
```
GET /storage/files/{uuid}
```

**Ответ (200):**
- Content-Type: {mime-type файла}
- Content-Disposition: attachment; filename="{originalName}"
- Body: бинарное содержимое файла

### 4. Удаление файла
```
DELETE /storage/files/{uuid}
```

**Ответ (204):** No Content

## База данных

### Создание таблицы

Выполните SQL миграцию:
```bash
mysql -u username -p database_name < sql/storage/storage_files.sql
```

Или вручную:
```sql
CREATE TABLE IF NOT EXISTS `storage_files` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` VARCHAR(36) NOT NULL UNIQUE,
    `original_name` VARCHAR(255) NOT NULL,
    `storage_name` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `size` INT UNSIGNED NOT NULL,
    `path` VARCHAR(500) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_uuid` (`uuid`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Директория хранения

Файлы хранятся в:
```
storage/files/
```

### Права доступа

На сервере установите права:
```bash
chmod -R 755 storage
chown -R www-data:www-data storage  # или нужный пользователь веб-сервера
```

## Примеры использования

### cURL примеры

**Загрузка файла:**
```bash
curl -X POST https://tradeapp.xsdk.ru/storage/files \
  -F "file=@/path/to/document.pdf"
```

**Список файлов:**
```bash
curl https://tradeapp.xsdk.ru/storage/files
```

**Скачивание файла:**
```bash
curl -O https://tradeapp.xsdk.ru/storage/files/550e8400-e29b-41d4-a716-446655440000
```

**Удаление файла:**
```bash
curl -X DELETE https://tradeapp.xsdk.ru/storage/files/550e8400-e29b-41d4-a716-446655440000
```

## Безопасность

### Текущая реализация

- ✅ Файлы хранятся вне `public_html` (недоступны напрямую)
- ✅ Доступ только через API
- ✅ UUID-именование (скрывает оригинальные имена)

### Рекомендации для продакшена

1. **Добавить авторизацию:**
   - Только авторизованные пользователи могут загружать/удалять
   - JWT-токен в заголовке Authorization

2. **Валидация файлов:**
   - Ограничение размера (max upload size)
   - Проверка MIME-типов (whitelist)
   - Проверка расширений

3. **Квоты:**
   - Ограничение на количество файлов на пользователя
   - Ограничение на общий объем

4. **Антивирус:**
   - Сканирование загруженных файлов

## Расширение модуля

Можно добавить:
- Генерацию thumbnails для изображений
- Поддержку папок/категорий
- Версионирование файлов
- Временные ссылки для скачивания
- Статистику использования
