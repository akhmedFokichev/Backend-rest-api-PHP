<?php
/**
 * dashboard.php — главная страница админки после входа.
 *
 * Назначение: сводка, ссылки на разделы, информация о роли пользователя.
 */
?>
<div class="row">
  <div class="col-lg-4 col-6">
    <div class="small-box bg-info">
      <div class="inner">
        <h3>Admin</h3>
        <p>AdminLTE 3 + PHP</p>
      </div>
      <div class="icon"><i class="fas fa-desktop"></i></div>
    </div>
  </div>
  <div class="col-lg-4 col-6">
    <div class="small-box bg-success">
      <div class="inner">
        <h3>Slim</h3>
        <p>/api/v1/*</p>
      </div>
      <div class="icon"><i class="fas fa-server"></i></div>
    </div>
  </div>
  <div class="col-lg-4 col-6">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3><?= htmlspecialchars($user['roleLabel'] ?? '—') ?></h3>
        <p>ваша роль</p>
      </div>
      <div class="icon"><i class="fas fa-user-shield"></i></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Добро пожаловать, <?= htmlspecialchars($user['name'] ?? $user['login'] ?? '') ?></h3>
  </div>
  <div class="card-body">
    <p>Админка: <code>/admin</code>, API: <code>/api/v1</code>.</p>
    <p>Данные через proxy: <code>/api/proxy/*</code> → Slim.</p>
    <?php if (\App\Core\Auth::can('users.view')): ?>
      <p><a href="<?= htmlspecialchars(\App\Core\Url::to('users')) ?>">Список пользователей</a> (требует роль Moderator+).</p>
    <?php endif; ?>
  </div>
</div>
