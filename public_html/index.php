<?php

/**
 * index.php — лендинг проекта на корне сайта (/).
 *
 * Назначение: презентация платформы для заказчиков и конечных пользователей.
 */

try {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl = $scheme . '://' . $host;
    $cssFile = __DIR__ . '/assets/css/landing.css';

    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Landing error: ' . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Identity — безопасная авторизация и управление пользователями для вашего мобильного приложения.">
  <title>Identity — платформа авторизации</title>
  <style><?php if (is_file($cssFile)) { readfile($cssFile); } ?></style>
</head>
<body>
  <div class="bg-glow"></div>

  <div class="container">
    <header class="site-header">
      <div class="brand">
        <div class="brand-mark">ID</div>
        <span>Identity Platform</span>
      </div>
      <nav class="nav">
        <a href="#about">О сервисе</a>
        <a href="#benefits">Преимущества</a>
        <a href="#how">Как это работает</a>
        <a href="/doc.php">Документация</a>
      </nav>
      <a class="btn btn-primary" href="/admin/login">Войти</a>
    </header>

    <section class="hero hero-home">
      <div>
        <div class="eyebrow">Платформа для вашего мобильного продукта</div>
        <h1>Безопасный вход и <span>управление пользователями</span> без лишней сложности</h1>
        <p class="lead">
          Identity помогает запустить мобильное приложение с готовой системой регистрации,
          авторизации и личных профилей. Ваши пользователи входят за секунды — вы управляете
          доступом из удобной веб-панели.
        </p>
        <div class="hero-actions">
          <a class="btn btn-primary" href="/admin/login">Войти в панель</a>
          <a class="btn btn-secondary" href="#how">Узнать, как это работает</a>
        </div>
        <div class="status-row">
          <span class="status-pill"><span class="status-dot"></span> Сервис работает</span>
          <span>Подходит для iOS, Android и веб-клиентов</span>
        </div>
      </div>

      <div class="hero-card hero-card-visual">
        <div class="visual-stack">
          <div class="visual-item">
            <span class="visual-icon">📱</span>
            <div>
              <strong>Мобильное приложение</strong>
              <p>Регистрация и вход пользователей</p>
            </div>
          </div>
          <div class="visual-item">
            <span class="visual-icon">🔐</span>
            <div>
              <strong>Защищённый доступ</strong>
              <p>Роли, токены и личные профили</p>
            </div>
          </div>
          <div class="visual-item">
            <span class="visual-icon">🖥</span>
            <div>
              <strong>Панель управления</strong>
              <p>Модерация и администрирование</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section" id="about">
      <h2>Что такое Identity</h2>
      <p class="section-lead">
        Это готовая платформа, которая берёт на себя всё, что связано с учётными записями
        в вашем цифровом продукте — от первого входа до управления командой и пользователями.
      </p>
      <div class="cards">
        <article class="card">
          <div class="card-icon blue">👤</div>
          <h3>Для пользователей</h3>
          <p>Быстрая регистрация, удобный вход и личный профиль в мобильном приложении.</p>
        </article>
        <article class="card">
          <div class="card-icon violet">🏢</div>
          <h3>Для заказчика</h3>
          <p>Готовое решение вместо разработки авторизации с нуля — быстрее выход на рынок.</p>
        </article>
        <article class="card">
          <div class="card-icon green">⚙️</div>
          <h3>Для команды проекта</h3>
          <p>Веб-панель для просмотра пользователей, ролей и контроля доступа.</p>
        </article>
      </div>
    </section>

    <section class="section" id="benefits">
      <h2>Почему это удобно</h2>
      <p class="section-lead">Сервис закрывает типовые задачи, с которыми сталкивается каждый мобильный продукт.</p>
      <div class="cards">
        <article class="card">
          <div class="card-icon green">✓</div>
          <h3>Быстрый запуск</h3>
          <p>Не нужно месяцами проектировать и тестировать собственную систему входа.</p>
        </article>
        <article class="card">
          <div class="card-icon blue">✓</div>
          <h3>Надёжность</h3>
          <p>Централизованное хранение учётных записей и разграничение прав доступа.</p>
        </article>
        <article class="card">
          <div class="card-icon violet">✓</div>
          <h3>Масштабируемость</h3>
          <p>Подходит для MVP, пилотного запуска и дальнейшего роста аудитории.</p>
        </article>
      </div>
    </section>

    <section class="section" id="how">
      <h2>Как это работает</h2>
      <p class="section-lead">Три простых шага — без технических деталей.</p>
      <div class="steps steps-home">
        <article class="step">
          <div class="step-num">1</div>
          <h3>Пользователь открывает приложение</h3>
          <p>Регистрируется или входит по логину и паролю — всё происходит внутри вашего продукта.</p>
        </article>
        <article class="step">
          <div class="step-num">2</div>
          <h3>Identity обрабатывает доступ</h3>
          <p>Платформа проверяет учётные данные, выдаёт доступ и сохраняет профиль пользователя.</p>
        </article>
        <article class="step">
          <div class="step-num">3</div>
          <h3>Вы управляете из панели</h3>
          <p>Администраторы и модераторы видят пользователей, роли и могут управлять доступом.</p>
        </article>
      </div>

      <div class="cta">
        <div>
          <h3>Нужны технические детали?</h3>
          <p>Для разработчиков и интеграторов — отдельная страница с описанием API, эндпоинтов и админ-панели.</p>
        </div>
        <a class="btn btn-primary" href="/doc.php">Открыть документацию</a>
      </div>
    </section>

    <section class="section" id="contact">
      <h2>Для кого этот сервис</h2>
      <p class="section-lead">Identity подойдёт, если вы запускаете или развиваете мобильный продукт и хотите не отвлекаться на инфраструктуру входа.</p>
      <div class="cards">
        <article class="card">
          <div class="card-icon blue">🚀</div>
          <h3>Стартапы и MVP</h3>
          <p>Быстро проверить гипотезу и вывести приложение к первым пользователям.</p>
        </article>
        <article class="card">
          <div class="card-icon violet">📲</div>
          <h3>Мобильные команды</h3>
          <p>Сфокусироваться на UX приложения, а авторизацию доверить готовой платформе.</p>
        </article>
        <article class="card">
          <div class="card-icon green">🤝</div>
          <h3>Заказчики и агентства</h3>
          <p>Понятный сервис с веб-панелью — удобно показывать и передавать в эксплуатацию.</p>
        </article>
      </div>
    </section>

    <footer class="site-footer">
      <div>Identity Platform · Авторизация и управление пользователями</div>
      <div style="margin-top:0.35rem;">
        <a href="/doc.php" style="color:#93c5fd;">Документация для разработчиков</a>
        · <a href="/admin/login" style="color:#93c5fd;">Вход в панель</a>
      </div>
    </footer>
  </div>
</body>
</html>
