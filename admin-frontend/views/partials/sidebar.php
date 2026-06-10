<?php

use App\Core\Auth;
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="/admin" class="brand-link">
    <span class="brand-text font-weight-light">Backend Admin</span>
  </a>

  <div class="sidebar">
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
        <li class="nav-item">
          <a href="/admin" class="nav-link<?= ($_SERVER['REQUEST_URI'] ?? '') === '/admin' ? ' active' : '' ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Дашборд</p>
          </a>
        </li>
        <?php if (Auth::can('users.view')): ?>
        <li class="nav-item">
          <a href="/admin/users" class="nav-link<?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/users') ? ' active' : '' ?>">
            <i class="nav-icon fas fa-users"></i>
            <p>Пользователи</p>
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</aside>
