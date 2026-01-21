# Архитектура модуля Storage

## 🏗️ Структура модуля

```
Storage Module
│
├── Domain Layer (Ядро)
│   ├── Entity/
│   │   └── File.php                          # Сущность файла
│   └── Repository/
│       └── FileRepositoryInterface.php        # Контракт репозитория
│
├── Application Layer (Бизнес-логика)
│   ├── UploadFileUseCase.php                 # UC: Загрузка файла
│   ├── ListFilesUseCase.php                  # UC: Список файлов
│   ├── GetFileUseCase.php                    # UC: Получение файла
│   └── DeleteFileUseCase.php                 # UC: Удаление файла
│
├── Infrastructure Layer (Реализация)
│   └── FileRepositoryMysql.php               # Репозиторий для MySQL
│
└── Http Layer (Представление)
    └── Controller/
        └── FileController.php                # HTTP контроллер
```

---

## 🔄 Жизненный цикл запроса: Загрузка файла

### Пример: POST /storage/files

```
┌──────────────────────────────────────────────────────────────┐
│ 1. HTTP Request                                              │
│    POST /storage/files                                       │
│    Content-Type: multipart/form-data                         │
│    Body: file (binary)                                       │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────────────┐
│ 2. index.php (Точка входа)                                   │
│    - Автозагрузка классов                                    │
│    - Создание зависимостей:                                  │
│      • PDO connection                                        │
│      • FileRepositoryMysql($pdo)                            │
│      • UploadFileUseCase($repo, $storageDir)                │
│      • FileController($uploadUC, ...)                        │
│    - Настройка Slim Framework                               │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────────────┐
│ 3. Slim Router (routes.php)                                  │
│    POST /storage/files → FileController::upload()            │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────────────┐
│ 4. FileController::upload()                                  │
│    • Получает UploadedFile из Request                       │
│    • Конвертирует в массив для Use Case                     │
│    • Вызывает: $this->uploadUC->execute($fileArray)         │
│    • Форматирует ответ в JSON                               │
│    • Обрабатывает исключения → HTTP 400                     │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────────────┐
│ 5. UploadFileUseCase::execute()                              │
│    • Валидация файла (is_uploaded_file, error codes)        │
│    • Генерация UUID                                         │
│    • Определение storage name (UUID + extension)            │
│    • Создание директории storage/files (если нет)           │
│    • move_uploaded_file() → физическое сохранение           │
│    • new File(...) → создание Domain сущности               │
│    • $this->fileRepo->add($file) → сохранение в БД          │
│    • return $file                                           │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ├─────────────┬─────────────┐
                       ▼             ▼             ▼
            ┌──────────────┐ ┌──────────┐ ┌────────────┐
            │  File System │ │   File   │ │ Repository │
            │              │ │  Entity  │ │            │
            │ storage/     │ │          │ │ MySQL DB   │
            │  files/      │ │ uuid     │ │ INSERT...  │
            │  {uuid}.ext  │ │ name     │ │            │
            └──────────────┘ │ size     │ └────────────┘
                             │ mimeType │
                             └──────────┘
                                   │
                                   │ (возврат)
                                   ▼
┌──────────────────────────────────────────────────────────────┐
│ 6. HTTP Response                                             │
│    Status: 201 Created                                       │
│    Content-Type: application/json                            │
│    Body:                                                     │
│    {                                                         │
│      "id": 1,                                                │
│      "uuid": "550e8400-...",                                 │
│      "originalName": "document.pdf",                         │
│      "mimeType": "application/pdf",                          │
│      "size": 1024000,                                        │
│      "createdAt": "2024-01-20 12:00:00"                      │
│    }                                                         │
└──────────────────────────────────────────────────────────────┘
```

---

## 🎯 Разделение ответственности

### FileController (Http слой)
**Отвечает за:**
- ✅ HTTP протокол (Request/Response)
- ✅ Валидация формата запроса
- ✅ Конвертация PSR-7 объектов
- ✅ HTTP статус коды
- ✅ Обработка HTTP-специфичных ошибок

**НЕ отвечает за:**
- ❌ Бизнес-логику
- ❌ Работу с файловой системой
- ❌ Работу с БД

---

### UploadFileUseCase (Application слой)
**Отвечает за:**
- ✅ Бизнес-правила (валидация файла)
- ✅ Оркестрацию процесса загрузки
- ✅ Генерацию UUID
- ✅ Координацию File System + Database

**НЕ отвечает за:**
- ❌ HTTP детали
- ❌ Конкретную реализацию хранилища
- ❌ SQL-запросы

---

### FileRepositoryMysql (Infrastructure слой)
**Отвечает за:**
- ✅ SQL-запросы
- ✅ Маппинг Entity ↔ таблица
- ✅ Работу с PDO

**НЕ отвечает за:**
- ❌ Бизнес-логику
- ❌ HTTP
- ❌ Файловую систему

---

### File (Domain слой)
**Отвечает за:**
- ✅ Структуру данных
- ✅ Инварианты (правила которые всегда должны выполняться)
- ✅ Методы преобразования (toArray)

**НЕ зависит от:**
- ❌ БД
- ❌ HTTP
- ❌ Фреймворков
- ❌ Внешних библиотек

---

## 🔌 Dependency Injection

```php
// index.php

// 1. Создаем инфраструктуру
$pdo = new PDO(...);
$storageDir = __DIR__ . '/../storage/files';

// 2. Создаем репозиторий (Infrastructure)
$fileRepo = new FileRepositoryMysql($pdo);

// 3. Создаем Use Cases (Application)
$uploadFileUC = new UploadFileUseCase($fileRepo, $storageDir);
$listFilesUC = new ListFilesUseCase($fileRepo);
$getFileUC = new GetFileUseCase($fileRepo, $storageDir);
$deleteFileUC = new DeleteFileUseCase($fileRepo, $storageDir);

// 4. Создаем контроллер (Http)
$storage = new FileController($uploadFileUC, $listFilesUC, $getFileUC, $deleteFileUC);

// 5. Передаем в роуты
$routes($app, $identity, $country, $storage, $docs);
```

**Зависимости:**
```
FileController 
    → UploadFileUseCase
        → FileRepositoryInterface (интерфейс!)
            → FileRepositoryMysql (реализация)
                → PDO
```

---

## 📈 Масштабируемость

### Легко заменить хранилище

Если нужно переключиться с локального хранилища на S3:

1. Создаете новый репозиторий:
   ```php
   class FileRepositoryS3 implements FileRepositoryInterface {
       // Реализация для AWS S3
   }
   ```

2. Меняете в `index.php`:
   ```php
   // Было:
   $fileRepo = new FileRepositoryMysql($pdo);
   
   // Стало:
   $fileRepo = new FileRepositoryS3($s3Client);
   ```

3. Use Cases, Controller, Entity остаются **БЕЗ ИЗМЕНЕНИЙ**!

### Легко добавить новые функции

Хотите добавить генерацию thumbnails?

1. Создаете `GenerateThumbnailUseCase`
2. Добавляете метод в `FileController`
3. Добавляете роут

Всё остальное работает как прежде!

---

## 🎨 Сравнение модулей

| Аспект | Identity | Reference | Storage |
|--------|----------|-----------|---------|
| **Сущности** | User, Token, Session | Country | File |
| **Use Cases** | 3 | 4 (CRUD) | 4 (Upload/List/Get/Delete) |
| **Репозитории** | 2 (User, Session) | 1 (Country) | 1 (File) |
| **API Endpoints** | 3 | 4 | 4 |
| **Тип операций** | Auth/Session | CRUD | File management |

---

## ✨ Что получили

Полноценный модуль Storage с:
- ✅ Чистой архитектурой (4 слоя)
- ✅ Разделением ответственности
- ✅ Тестируемостью
- ✅ Масштабируемостью
- ✅ OpenAPI документацией
- ✅ Единым стилем с другими модулями
