# Развёртывание на хостинге

## Вариант A: корень сайта = корень проекта (рекомендуется при Forbidden)

Загрузите на хостинг **всю папку проекта** так, чтобы в корне сайта лежали:

- `index.php`
- `.htaccess`
- папка `vendor/`
- файл `routes.php`
- папка `src/`

В панели хостинга укажите **корневую папку (Document Root)** = эта же папка (куда загрузили проект).

Тогда запрос к `https://identity.xsdk.ru/` обработает `index.php` и вернёт OK.

---

## Вариант B: корень сайта = public_html

Если в панели уже привязана папка `public_html`:

- Загрузите в неё содержимое нашей папки `public_html` (там уже есть свой `index.php` и `.htaccess`).
- Остальное (`vendor/`, `routes.php`, `src/`) загрузите **на уровень выше** `public_html` (в родительскую папку).

Иначе PHP не найдёт `vendor/autoload.php` и `routes.php`, т.к. в `public_html/index.php` указаны пути `../vendor` и `../routes.php`.

---

## Права

- Папки: **755**
- Файлы: **644**

## Проверка

Откройте в браузере: `https://identity.xsdk.ru/` — должна отображаться строка **OK**.

---

## Обновление с GitHub (git pull)

### Один раз на сервере

```bash
cd /path/to/project
git clone git@github.com:USER/REPO.git .
cp config/github-pull.example.php config/github-pull.local.php
# задайте secret и branch (master или main)
```

### Через HTTP (удалённо)

```bash
curl "https://identity.xsdk.ru/github-pull.php?token=YOUR_SECRET"
```

Или с заголовком:

```bash
curl -H "X-Deploy-Token: YOUR_SECRET" "https://identity.xsdk.ru/github-pull.php"
```

### Через SSH

```bash
php scripts/github-pull.php
```

**Нужно:** `git` и `exec()` на хостинге.

---

## Автодеплой через GitHub Actions

После push в `master`/`main` GitHub сам вызовет `github-pull.php` на сервере.

### 1. На сервере (один раз)

```bash
git clone git@github.com:USER/REPO.git .
cp config/github-pull.example.php config/github-pull.local.php
# secret в github-pull.local.php = тот же, что DEPLOY_TOKEN в GitHub
```

### 2. В GitHub репозитории

**Settings → Secrets and variables → Actions → New repository secret**

| Secret | Значение |
|--------|----------|
| `DEPLOY_TOKEN` | тот же token, что в `config/github-pull.local.php` |

Опционально **Variables**:

| Variable | Значение |
|----------|----------|
| `DEPLOY_URL` | `https://identity.xsdk.ru/github-pull.php` (если не задан — используется этот URL по умолчанию) |

### 3. Проверка

1. Сделай commit + push в `master`
2. Открой **Actions** в GitHub — job `Deploy to server` должен быть зелёным
3. На сервере код обновится через `git pull`

Ручной запуск: **Actions → Deploy to server → Run workflow**.

### Если Actions падает с `curl: (28) Couldn't connect to server`

GitHub Actions не может достучаться до хостинга (часто блокируются IP дата-центров).
Сайт при этом из браузера может открываться нормально.

**Решение: cron на сервере** (сервер сам тянет код с GitHub):

```bash
# каждые 5 минут
*/5 * * * * cd /home/c/cv82602/slim && /usr/bin/php scripts/github-pull.php >> var/pull.log 2>&1
```

Или через панель хостинга → **Cron** → та же команда.

CLI-режим `scripts/github-pull.php` token не требует — нужен только `git` и `exec()`.

**Альтернатива:** self-hosted GitHub Runner на сервере (Actions запускаются локально, без входящего HTTP).
