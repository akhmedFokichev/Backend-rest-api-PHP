<?php

/**
 * sidebar.php — боковое меню админки.
 *
 * Назначение: навигация в разметке AdminLTE 3 (brand, user-panel, treeview).
 */

use App\Core\Auth;
use App\Core\Url;

$currentUri = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/', '/') ?: '/';
$user = Auth::user();
$homeUrl = Url::to();
$usersUrl = Url::to('users');

$isHome = $currentUri === rtrim($homeUrl, '/') || $currentUri === $homeUrl;
$isUsers = str_starts_with($currentUri, $usersUrl);
$isPlatformOpen = $isHome || $isUsers;

$displayName = $user['login'] ?? 'Пользователь';
$roleLabel = $user['roleLabel'] ?? '';
$initial = mb_strtoupper(mb_substr($displayName, 0, 1));
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="<?= htmlspecialchars($homeUrl) ?>" class="brand-link">
    <span class="brand-image brand-image-text elevation-3">QK</span>
    <span class="brand-text font-weight-light">Quokka</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user panel (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <span class="img-circle elevation-2 user-avatar"><?= htmlspecialchars($initial) ?></span>
      </div>
      <div class="info">
        <a href="<?= htmlspecialchars($homeUrl) ?>" class="d-block"><?= htmlspecialchars($displayName) ?></a>
        <?php if ($roleLabel !== ''): ?>
          <small class="text-muted d-block"><?= htmlspecialchars($roleLabel) ?></small>
        <?php endif; ?>
      </div>
    </div>

    <!-- SidebarSearch Form -->
    <div class="form-inline">
      <div class="input-group" data-widget="sidebar-search">
        <input class="form-control form-control-sidebar" type="search" placeholder="Поиск" aria-label="Search">
        <div class="input-group-append">
          <button class="btn btn-sidebar">
            <i class="fas fa-search fa-fw"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Add icons to the links using the .nav-icon class
             with font-awesome or any other icon font library -->
        <li class="nav-item<?= $isPlatformOpen ? ' menu-open' : '' ?>">
          <a href="#" class="nav-link<?= $isPlatformOpen ? ' active' : '' ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
              Платформа
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= htmlspecialchars($homeUrl) ?>" class="nav-link<?= $isHome ? ' active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Главная</p>
              </a>
            </li>
            <?php if (Auth::can('users.view')): ?>
            <li class="nav-item">
              <a href="<?= htmlspecialchars($usersUrl) ?>" class="nav-link<?= $isUsers ? ' active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Пользователи</p>
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </li>

        <li class="nav-header">ССЫЛКИ</li>

        <li class="nav-item">
          <a href="/" class="nav-link" target="_blank" rel="noopener">
            <i class="nav-icon fas fa-globe"></i>
            <p>
              Сайт
              <span class="right badge badge-info">new</span>
            </p>
          </a>
        </li>

        <li class="nav-item">
          <a href="/doc.php" class="nav-link" target="_blank" rel="noopener">
            <i class="nav-icon fas fa-book"></i>
            <p>Документация</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= htmlspecialchars(Url::to('logout')) ?>" class="nav-link">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Выйти</p>
          </a>
        </li>
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>
