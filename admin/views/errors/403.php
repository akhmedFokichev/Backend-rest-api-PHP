<?php
/**
 * 403.php — страница «доступ запрещён».
 *
 * Назначение: показывается при недостаточных правах (Auth::requirePermission).
 */
?>
<div class="login-box" style="width: auto; max-width: 480px;">
  <div class="card">
    <div class="card-body login-card-body text-center">
      <h1 class="text-danger mb-3"><i class="fas fa-ban"></i> 403</h1>
      <p class="text-muted">Недостаточно прав для просмотра этой страницы</p>
      <a href="<?= htmlspecialchars(\App\Core\Url::to()) ?>" class="btn btn-primary mt-2">
        <i class="fas fa-home mr-1"></i> На главную
      </a>
    </div>
  </div>
</div>
