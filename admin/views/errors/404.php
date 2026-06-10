<?php
/**
 * 404.php — страница «маршрут не найден».
 *
 * Назначение: неизвестный URL внутри админ-роутера.
 */
?>
<div class="login-box" style="width: auto; max-width: 480px;">
  <div class="card card-outline card-warning">
    <div class="card-body text-center">
      <h1 class="text-warning">404</h1>
      <p>Страница не найдена.</p>
      <a href="<?= htmlspecialchars(\App\Core\Url::to()) ?>" class="btn btn-primary">На дашборд</a>
    </div>
  </div>
</div>
