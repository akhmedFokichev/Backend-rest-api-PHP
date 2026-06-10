<?php use App\Core\Url; ?>
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
  </ul>

  <ul class="navbar-nav ml-auto">
    <li class="nav-item d-none d-sm-inline-block">
      <span class="nav-link text-muted"><?= htmlspecialchars($user['login'] ?? '') ?></span>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="<?= htmlspecialchars(Url::to('logout')) ?>" title="Выход"><i class="fas fa-sign-out-alt"></i></a>
    </li>
  </ul>
</nav>
