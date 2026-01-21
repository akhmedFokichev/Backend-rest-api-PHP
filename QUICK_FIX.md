# ⚡ Быстрое исправление HTTP 500

## ❌ Проблема
Сервер возвращает HTTP 500 - внутренняя ошибка.

## ✅ Решение (3 команды)

Подключитесь к серверу по SSH и выполните:

```bash
# 1. Обновите конфигурацию
cat > /home/c/cv82602/tradeApp/config/env.local.php << 'EOF'
<?php
return [
    'APP_NAME'   => 'Backend REST API',
    'APP_VERSION'=> '1.0.0',
    'HASH_KEY'   => 'your-hash-key-change-me',
    'CLIENT_IDS' => 'web_app,mobile_app',
    'JWT_SECRET' => 'your-jwt-secret-change-me',
    'DB_HOST'    => 'localhost',
    'DB_NAME'    => 'cv82602_trade',
    'DB_USER'    => 'cv82602_trade',
    'DB_PASS'    => 'CHW423Br',
];
EOF

# 2. Создайте таблицы в БД
mysql -u cv82602_trade -pCHW423Br cv82602_trade < /home/c/cv82602/tradeApp/sql/install_all.sql

# 3. Создайте директорию storage
mkdir -p /home/c/cv82602/tradeApp/storage/files
chmod -R 755 /home/c/cv82602/tradeApp/storage
```

## ✅ Проверка

```bash
# Должны быть 4 таблицы
mysql -u cv82602_trade -pCHW423Br -e "SHOW TABLES;" cv82602_trade

# Должна вернуться конфигурация
php /home/c/cv82602/tradeApp/test-config.php
```

## 🌐 После этого проверьте

- https://tradeapp.xsdk.ru/ → должно быть "OK"
- https://tradeapp.xsdk.ru/swagger-ui.html → должен загрузиться Swagger

---

**Если не работает, смотрите логи:**
```bash
tail -50 /home/c/cv82602/logs/error.log
```
