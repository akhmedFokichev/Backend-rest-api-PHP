<?php
/**
 * 404.php — страница «маршрут не найден».
 *
 * Назначение: неизвестный URL внутри админ-роутера.
 */
?>
<div class="login-box" style="width: auto; max-width: 480px;">
  <div class="card">
    <div class="card-body login-card-body text-center">
      <h1 class="text-warning mb-3"><i class="fas fa-exclamation-triangle"></i> 404</h1>
      <p class="text-muted">Страница не найдена</p>
      <a href="<?= htmlspecialchars(\App\Core\Url::to()) ?>" class="btn btn-primary mt-2">
        <i class="fas fa-home mr-1"></i> На главную
      </a>
    </div>
  </div>
</div>
