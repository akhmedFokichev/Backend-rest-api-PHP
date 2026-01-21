# ✅ Чек-лист деплоя модуля Storage

## 📦 Что создано

### Domain слой
- ✅ `src/Domain/Storage/Entity/File.php` - сущность файла
- ✅ `src/Domain/Storage/Repository/FileRepositoryInterface.php` - интерфейс репозитория

### Application слой  
- ✅ `src/Application/Storage/UploadFileUseCase.php` - загрузка файла
- ✅ `src/Application/Storage/ListFilesUseCase.php` - список файлов
- ✅ `src/Application/Storage/GetFileUseCase.php` - получение файла
- ✅ `src/Application/Storage/DeleteFileUseCase.php` - удаление файла

### Infrastructure слой
- ✅ `src/Infrastructure/Storage/FileRepositoryMysql.php` - репозиторий MySQL

### Http слой
- ✅ `src/Http/Controller/Storage/FileController.php` - HTTP контроллер с OpenAPI аннотациями

### Интеграция
- ✅ `src/Http/routes.php` - добавлены роуты `/storage/files`
- ✅ `src/Http/OpenApi.php` - добавлен тег Storage
- ✅ `public_html/index.php` - инъекция зависимостей Storage

### База данных
- ✅ `sql/storage/storage_files.sql` - SQL миграция

### Хранилище
- ✅ `storage/files/` - директория для файлов
- ✅ `storage/.gitignore` - игнорирование файлов в git

---

## 🚀 Шаги деплоя на сервер

### [ ] 1. Загрузите код на сервер

Через FTP/SFTP загрузите в `/home/c/cv82602/tradeApp/`:

```
src/Domain/Storage/
src/Application/Storage/
src/Infrastructure/Storage/
src/Http/Controller/Storage/
src/Http/routes.php (обновлен)
src/Http/OpenApi.php (обновлен)
public_html/index.php (обновлен)
sql/storage/
```

### [ ] 2. Создайте таблицу в БД

Подключитесь к MySQL и выполните:

```bash
mysql -u cv82602_slimdev -p cv82602_slimdev < sql/storage/storage_files.sql
```

Или через phpMyAdmin:
1. Откройте базу `cv82602_slimdev`
2. Выполните SQL из файла `sql/storage/storage_files.sql`

### [ ] 3. Создайте директорию storage на сервере

```bash
cd /home/c/cv82602/tradeApp
mkdir -p storage/files
chmod -R 755 storage
```

### [ ] 4. Проверьте права доступа

Убедитесь, что веб-сервер может записывать в `storage/files`:

```bash
# Проверьте владельца директории
ls -la storage/

# Если нужно, измените владельца
chown -R www-data:www-data storage/  # или нужный пользователь
```

### [ ] 5. Проверьте лимиты загрузки в PHP

В файле `php.ini` или через панель хостинга установите:

```ini
file_uploads = On
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
```

### [ ] 6. Проверьте Swagger UI

Откройте:
```
https://tradeapp.xsdk.ru/swagger-ui.html
```

Должна появиться новая секция **Storage** с эндпоинтами:
- POST /storage/files
- GET /storage/files
- GET /storage/files/{uuid}
- DELETE /storage/files/{uuid}

### [ ] 7. Протестируйте API

**Загрузка файла:**
```bash
curl -X POST https://tradeapp.xsdk.ru/storage/files \
  -F "file=@test.txt"
```

Ответ:
```json
{
  "id": 1,
  "uuid": "550e8400-...",
  "originalName": "test.txt",
  "mimeType": "text/plain",
  "size": 1024,
  "createdAt": "2024-01-20 12:00:00"
}
```

**Список файлов:**
```bash
curl https://tradeapp.xsdk.ru/storage/files
```

**Скачивание:**
```bash
curl -O https://tradeapp.xsdk.ru/storage/files/550e8400-...
```

**Удаление:**
```bash
curl -X DELETE https://tradeapp.xsdk.ru/storage/files/550e8400-...
```

---

## ❗ Возможные проблемы

### Ошибка "Failed to move uploaded file"
- Проверьте права на директорию `storage/files` (должна быть 755 или 777)
- Проверьте владельца директории (должен быть пользователь веб-сервера)

### Ошибка "Table 'storage_files' doesn't exist"
- Выполните SQL миграцию из `sql/storage/storage_files.sql`

### Ошибка "File too large"
- Увеличьте `upload_max_filesize` и `post_max_size` в php.ini

### Ошибка в Swagger UI
- Проверьте, что загружены обновленные файлы
- Откройте `/api-docs.json` напрямую для проверки

---

## 📊 Итоговая структура модулей

Теперь у вас **3 модуля**:

### 1. Identity модуль
- Регистрация, логин, refresh токенов
- API: `/identity/*`

### 2. Reference модуль
- Управление справочниками (Country, ...)
- API: `/reference/*`

### 3. Storage модуль (НОВЫЙ!)
- Загрузка, хранение, скачивание, удаление файлов
- API: `/storage/*`

---

## 🎉 После деплоя

Модуль Storage будет полностью интегрирован в приложение и доступен через Swagger UI для тестирования!
