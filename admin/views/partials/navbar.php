<?php
/**
 * navbar.php — верхняя панель админки.
 *
 * Назначение: логин пользователя, роль и кнопка выхода.
 */
use App\Core\Url;
?>
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-md-inline-block">
      <span class="nav-link text-muted"><?= htmlspecialchars($pageTitle ?? '') ?></span>
    </li>
  </ul>

  <ul class="navbar-nav ml-auto align-items-center">
    <li class="nav-item d-none d-sm-inline-block mr-2">
      <span class="navbar-user-chip">
        <i class="fas fa-user-circle text-muted"></i>
        <span><?= htmlspecialchars($user['login'] ?? '') ?></span>
        <?php if (!empty($user['roleLabel'])): ?>
          <span class="role-pill"><?= htmlspecialchars($user['roleLabel']) ?></span>
        <?php endif; ?>
      </span>
    </li>
    <li class="nav-item">
      <a class="nav-link text-danger" href="<?= htmlspecialchars(Url::to('logout')) ?>" title="Выйти">
        <i class="fas fa-sign-out-alt"></i>
        <span class="d-none d-lg-inline ml-1">Выйти</span>
      </a>
    </li>
  </ul>
</nav>
