<?php

/**
 * sidebar.php — боковое меню админки.
 *
 * Назначение: навигация по разделам с учётом permissions пользователя.
 */

use App\Core\Auth;
use App\Core\Url;

$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$user = Auth::user();
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="<?= htmlspecialchars(Url::to()) ?>" class="brand-link">
    <span class="brand-quokka">
      <span class="brand-quokka-mark">QK</span>
      <span class="brand-quokka-text">
        <strong>Quokka</strong>
        <span>Админ-панель</span>
      </span>
    </span>
  </a>

  <div class="sidebar d-flex flex-column" style="height: calc(100vh - 57px);">
    <nav class="mt-2 flex-grow-1">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
        <li class="nav-header">ОСНОВНОЕ</li>
        <li class="nav-item">
          <a href="<?= htmlspecialchars(Url::to()) ?>" class="nav-link<?= rtrim($currentUri, '/') === Url::to() ? ' active' : '' ?>">
            <i class="nav-icon fas fa-home"></i>
            <p>Главная</p>
          </a>
        </li>
        <?php if (Auth::can('users.view')): ?>
        <li class="nav-item">
          <a href="<?= htmlspecialchars(Url::to('users')) ?>" class="nav-link<?= str_starts_with($currentUri, Url::to('users')) ? ' active' : '' ?>">
            <i class="nav-icon fas fa-users"></i>
            <p>Пользователи</p>
          </a>
        </li>
        <?php endif; ?>

        <li class="nav-header">ССЫЛКИ</li>
        <li class="nav-item">
          <a href="/" class="nav-link" target="_blank" rel="noopener">
            <i class="nav-icon fas fa-globe"></i>
            <p>Сайт</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/doc.php" class="nav-link" target="_blank" rel="noopener">
            <i class="nav-icon fas fa-book"></i>
            <p>Документация</p>
          </a>
        </li>
      </ul>
    </nav>

    <?php if ($user): ?>
    <div class="sidebar-user-panel">
      <div class="user-name"><?= htmlspecialchars($user['login'] ?? '') ?></div>
      <span class="badge badge-light user-role"><?= htmlspecialchars($user['roleLabel'] ?? 'Пользователь') ?></span>
    </div>
    <?php endif; ?>
  </div>
</aside>
