<?php

/**
 * doc.php — техническая документация для разработчиков.
 *
 * Назначение: описание API, админ-панели и быстрого старта интеграции.
 */

try {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl = $scheme . '://' . $host;
    $apiBase = $baseUrl . '/api/v1';
    $cssFile = __DIR__ . '/assets/css/landing.css';

    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Doc error: ' . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Документация Quokka: REST API, админ-панель и быстрый старт для разработчиков.">
  <title>Документация — Quokka</title>
  <style><?php if (is_file($cssFile)) { readfile($cssFile); } ?></style>
</head>
<body>
  <div class="bg-glow"></div>

  <div class="container">
    <header class="site-header">
      <div class="brand">
        <a href="/" class="brand" style="text-decoration:none;">
          <div class="brand-mark">QK</div>
          <span>Quokka</span>
        </a>
      </div>
      <nav class="nav">
        <a href="/">Главная</a>
        <a href="#start">Быстрый старт</a>
        <a href="#api">API</a>
        <a href="#admin">Админка</a>
      </nav>
      <a class="btn btn-primary" href="/admin/login">Войти в панель</a>
    </header>

    <section class="hero hero-doc">
      <div>
        <div class="eyebrow">Для разработчиков и интеграторов</div>
        <h1>Документация <span>API</span> и админ-панели</h1>
        <p class="lead">
          Техническое описание эндпоинтов, ролей, авторизации и веб-интерфейса для модераторов.
          Используйте эту страницу при подключении мобильного приложения или настройке backend.
        </p>
        <div class="hero-actions">
          <a class="btn btn-primary" href="<?= htmlspecialchars($apiBase) ?>/db-check">Проверить API</a>
          <a class="btn btn-secondary" href="/admin/login">Админ-панель</a>
        </div>
        <div class="status-row">
          <span class="status-pill"><span class="status-dot"></span> Сервис доступен</span>
          <span>Base URL: <code><?= htmlspecialchars($apiBase) ?></code></span>
        </div>
      </div>

      <div class="hero-card">
        <pre><code>// Мобильное приложение — вход пользователя
POST <?= htmlspecialchars($apiBase) ?>/user/login
Content-Type: application/json

{
  "login": "user@example.com",
  "password": "secret123"
}

// Ответ
{
  "accessToken": "…",
  "tokenType": "Bearer",
  "role": 10,
  "roleLabel": "Пользователь"
}</code></pre>
      </div>
    </section>

    <section class="section" id="about">
      <h2>Архитектура проекта</h2>
      <p class="section-lead">Три части на одном домене: лендинг, REST API и панель администратора.</p>
      <div class="cards">
        <article class="card">
          <div class="card-icon blue">⚡</div>
          <h3>REST API</h3>
          <p>Slim + MySQL. JSON-эндпоинты для регистрации, логина, профилей и управления пользователями.</p>
        </article>
        <article class="card">
          <div class="card-icon violet">🛡</div>
          <h3>Роли и доступ</h3>
          <p>Guest, User, Moderator, Admin. Bearer-токены и middleware для защищённых маршрутов.</p>
        </article>
        <article class="card">
          <div class="card-icon green">📱</div>
          <h3>Для мобильного app</h3>
          <p>Клиент сохраняет <code>accessToken</code> и передаёт <code>Authorization: Bearer …</code> в каждом запросе.</p>
        </article>
      </div>
    </section>

    <section class="section" id="start">
      <h2>Быстрый старт</h2>
      <p class="section-lead">За несколько шагов можно поднять backend локально или на хостинге.</p>
      <div class="steps">
        <article class="step">
          <div class="step-num">1</div>
          <h3>Клонировать</h3>
          <p>Репозиторий с GitHub на сервер или локально.</p>
          <code>git clone … && composer install</code>
        </article>
        <article class="step">
          <div class="step-num">2</div>
          <h3>Настроить БД</h3>
          <p>Создать <code>config/db.local.php</code> и выполнить SQL из <code>sql/</code>.</p>
          <code>curl <?= htmlspecialchars($apiBase) ?>/db-check</code>
        </article>
        <article class="step">
          <div class="step-num">3</div>
          <h3>Запустить</h3>
          <p>Document root → <code>public_html</code>. Лендинг, API и админка на одном домене.</p>
          <code>php -S localhost:8080 -t public_html</code>
        </article>
        <article class="step">
          <div class="step-num">4</div>
          <h3>Подключить app</h3>
          <p>В мобильном клиенте укажите base URL API и используйте login/registration.</p>
          <code><?= htmlspecialchars($apiBase) ?></code>
        </article>
      </div>
    </section>

    <section class="section" id="api">
      <h2>API для мобильного приложения</h2>
      <p class="section-lead">Основные эндпоинты. Полная документация — в <code>docs/API_DOCUMENTATION.md</code> репозитория.</p>
      <div class="endpoints">
        <div class="endpoint-group">
          <h3>Публичные</h3>
          <div class="endpoint">
            <span class="method post">POST</span>
            <div><code>/api/v1/user/registration</code><br><span>Регистрация нового пользователя</span></div>
          </div>
          <div class="endpoint">
            <span class="method post">POST</span>
            <div><code>/api/v1/user/login</code><br><span>Вход, выдача accessToken</span></div>
          </div>
          <div class="endpoint">
            <span class="method get">GET</span>
            <div><code>/api/v1/db-check</code><br><span>Проверка подключения к БД</span></div>
          </div>
        </div>
        <div class="endpoint-group">
          <h3>С Bearer-токеном</h3>
          <div class="endpoint">
            <span class="method get">GET</span>
            <div><code>/api/v1/me</code><br><span>Проверка авторизации</span></div>
          </div>
          <div class="endpoint">
            <span class="method get">GET</span>
            <div><code>/api/v1/me/profile</code><br><span>Свой профиль</span></div>
          </div>
          <div class="endpoint">
            <span class="method put">PUT</span>
            <div><code>/api/v1/me/profile</code><br><span>Обновить имя, телефон, аватар</span></div>
          </div>
          <div class="endpoint">
            <span class="method get">GET</span>
            <div><code>/api/v1/user/list</code><br><span>Список пользователей (Moderator+)</span></div>
          </div>
          <div class="endpoint">
            <span class="method delete">DEL</span>
            <div><code>/api/v1/user/{id}</code><br><span>Удаление пользователя (Admin)</span></div>
          </div>
        </div>
      </div>

      <div class="cta">
        <div>
          <h3>Интеграция в мобильный клиент</h3>
          <p>После login сохраняйте токен и добавляйте заголовок <code>Authorization: Bearer &lt;token&gt;</code> ко всем защищённым запросам.</p>
        </div>
        <a class="btn btn-primary" href="<?= htmlspecialchars($apiBase) ?>/db-check">Проверить API</a>
      </div>
    </section>

    <section class="section" id="admin">
      <h2>Панель администратора</h2>
      <p class="section-lead">Веб-интерфейс на AdminLTE для модераторов и администраторов. Работает через тот же REST API.</p>
      <div class="cards">
        <article class="card">
          <div class="card-icon violet">🔐</div>
          <h3>Вход</h3>
          <p><a href="/admin/login">/admin/login</a> — авторизация через API, токен хранится в PHP-сессии.</p>
        </article>
        <article class="card">
          <div class="card-icon blue">👥</div>
          <h3>Пользователи</h3>
          <p><a href="/admin/users">/admin/users</a> — список, роли, удаление (для Admin).</p>
        </article>
        <article class="card">
          <div class="card-icon green">📊</div>
          <h3>Дашборд</h3>
          <p><a href="/admin">/admin</a> — сводка и быстрые ссылки после входа.</p>
        </article>
      </div>
    </section>

    <footer class="site-footer">
      <div>Quokka · Документация · PHP <?= PHP_VERSION ?></div>
      <div style="margin-top:0.35rem;">
        <a href="/" style="color:#93c5fd;">На главную</a>
        · Домен: <?= htmlspecialchars($baseUrl) ?>
        · API: <?= htmlspecialchars($apiBase) ?>
      </div>
    </footer>
  </div>
</body>
</html>
