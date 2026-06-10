<?php

declare(strict_types=1);

http_response_code(200);
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Identity</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: system-ui, -apple-system, sans-serif;
      background: #0f172a;
      color: #e2e8f0;
    }
    .card {
      max-width: 32rem;
      padding: 2rem;
      border-radius: 1rem;
      background: #1e293b;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
    }
    h1 { margin: 0 0 0.5rem; font-size: 1.75rem; }
    p { margin: 0 0 1.5rem; color: #94a3b8; line-height: 1.5; }
    .links { display: flex; gap: 0.75rem; flex-wrap: wrap; }
    a {
      color: #fff;
      background: #3b82f6;
      text-decoration: none;
      padding: 0.625rem 1rem;
      border-radius: 0.5rem;
      font-size: 0.95rem;
    }
    a.secondary { background: #334155; }
  </style>
</head>
<body>
  <main class="card">
    <h1>Identity</h1>
    <p>Платформа авторизации и управления пользователями.</p>
    <div class="links">
      <a href="/admin">Админ-панель</a>
      <a href="/api/v1/db-check" class="secondary">API status</a>
    </div>
  </main>
</body>
</html>
