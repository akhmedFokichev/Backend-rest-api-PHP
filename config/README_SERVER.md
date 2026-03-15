# Настройка на сервере (обязательно)

## Ошибка «Access denied for user ''@'localhost'»

Она означает: **на сервере нет или не подгружается файл с учётными данными БД.**

## Что сделать

Создайте на сервере файл:

```
/home/c/cv82602/tradeApp/config/env.local.php
```

Содержимое (подставьте свои данные БД):

```php
<?php
return [
    'APP_NAME'   => 'Backend REST API',
    'APP_VERSION'=> '1.0.0',
    'HASH_KEY'   => 'your-hash-key',
    'CLIENT_IDS' => 'web_app,mobile_app',
    'JWT_SECRET' => 'your-jwt-secret',

    'DB_HOST'    => 'localhost',
    'DB_NAME'    => 'cv82602_trade',   // ваша база
    'DB_USER'    => 'cv82602_trade',   // пользователь БД
    'DB_PASS'    => 'CHW423Br',        // пароль БД
];
```

## Как создать файл

### Вариант 1: по SSH

```bash
ssh cv82602@tradeapp.xsdk.ru

cat > /home/c/cv82602/tradeApp/config/env.local.php << 'ENVEOF'
<?php
return [
    'APP_NAME'   => 'Backend REST API',
    'APP_VERSION'=> '1.0.0',
    'HASH_KEY'   => 'your-hash-key',
    'CLIENT_IDS' => 'web_app,mobile_app',
    'JWT_SECRET' => 'your-jwt-secret',
    'DB_HOST'    => 'localhost',
    'DB_NAME'    => 'cv82602_trade',
    'DB_USER'    => 'cv82602_trade',
    'DB_PASS'    => 'CHW423Br',
];
ENVEOF

chmod 600 /home/c/cv82602/tradeApp/config/env.local.php
```

### Вариант 2: FTP/SFTP

1. Подключитесь к серверу.
2. Перейдите в каталог `.../tradeApp/config/`.
3. Создайте файл `env.local.php` с содержимым выше (или загрузите готовый с вашего компьютера).
4. Убедитесь, что файл в `config/`, рядом с `Config.php` и `env.local.example.php`.

### Вариант 3: скопировать из примера

```bash
cp /home/c/cv82602/tradeApp/config/env.local.example.php /home/c/cv82602/tradeApp/config/env.local.php
nano /home/c/cv82602/tradeApp/config/env.local.php
# Вписать реальные DB_NAME, DB_USER, DB_PASS и сохранить
```

## Проверка

После создания `env.local.php` откройте в браузере:

https://tradeapp.xsdk.ru/

Должно отображаться «OK», а не сообщение о конфигурации или ошибка доступа к БД.

## Важно

- Файл должен называться **именно** `env.local.php` и лежать в каталоге **config/**.
- `env.local.php` не должен попадать в Git (он уже в `.gitignore`), поэтому его создают вручную на каждом сервере.
