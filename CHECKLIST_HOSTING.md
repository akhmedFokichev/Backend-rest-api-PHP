# Что загрузить на хостинг (когда корень сайта = public_html)

Если в панели хостинга корневая папка сайта указана как **public_html**, то на сервере должна быть такая структура:

```
slim/                          ← папка проекта
├── public_html/               ← document root сайта
│   ├── index.php              ← лендинг (/)
│   ├── api/index.php          ← Slim API (/api/*)
│   ├── admin/index.php        ← админка (/admin/*)
│   └── .htaccess
├── api/
│   ├── routes.php
│   ├── bootstrap.php
│   └── src/
├── admin/                     ← код админ-панели
├── vendor/
└── config/
```

**URL на одном домене:**

| URL | Назначение |
|-----|------------|
| `/` | лендинг |
| `/api/v1/*` | REST API (Slim) |
| `/admin` | админ-панель |

После `git pull` статика админки (`/admin/assets/*`) отдаётся через PHP автоматически.
Symlink нужен только при отдельном запуске без bootstrap:

```bash
cd public_html/admin
ln -sf ../../admin/public/assets assets
```
