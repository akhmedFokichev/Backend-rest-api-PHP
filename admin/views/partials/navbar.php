<?php
/**
 * navbar.php — верхняя панель админки.
 *
 * Назначение: заголовок страницы из AppStore, пользователь и выход.
 */
use App\Core\Url;
?>
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-md-inline-block">
      <span class="nav-link text-muted" x-text="$store.app.pageTitle"><?= htmlspecialchars($pageTitle ?? '') ?></span>
    </li>
  </ul>

  <ul class="navbar-nav ml-auto align-items-center">
    <li class="nav-item d-none d-sm-inline-block mr-2">
      <span class="navbar-user-chip">
        <i class="fas fa-user-circle text-muted"></i>
        <span x-text="$store.app.user?.login || ''"><?= htmlspecialchars($user['login'] ?? '') ?></span>
        <span class="role-pill"
              x-show="$store.app.user?.roleLabel"
              x-text="$store.app.user?.roleLabel"
              x-cloak><?= htmlspecialchars($user['roleLabel'] ?? '') ?></span>
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
