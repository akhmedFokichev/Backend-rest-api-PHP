<?php
/**
 * dashboard.php — главная страница админки после входа.
 *
 * Назначение: приветствие, быстрые действия и сводка по роли пользователя.
 */

use App\Core\Auth;
use App\Core\Url;

$displayName = $user['login'] ?? 'пользователь';
$roleLabel = $user['roleLabel'] ?? '—';
$canViewUsers = Auth::can('users.view');
$canDelete = Auth::can('*');
?>
<div class="row">
  <div class="col-lg-4 col-md-6 mb-3">
    <div class="small-box stat-card bg-gradient-primary">
      <div class="inner">
        <h3><?= htmlspecialchars($displayName) ?></h3>
        <p>Вы вошли в систему</p>
      </div>
      <div class="icon"><i class="fas fa-user-check"></i></div>
    </div>
  </div>
  <div class="col-lg-4 col-md-6 mb-3">
    <div class="small-box stat-card bg-gradient-violet">
      <div class="inner">
        <h3><?= htmlspecialchars($roleLabel) ?></h3>
        <p>Ваша роль</p>
      </div>
      <div class="icon"><i class="fas fa-user-shield"></i></div>
    </div>
  </div>
  <div class="col-lg-4 col-md-6 mb-3">
    <div class="small-box stat-card bg-gradient-success">
      <div class="inner">
        <h3>Online</h3>
        <p>Сервис доступен</p>
      </div>
      <div class="icon"><i class="fas fa-check-circle"></i></div>
    </div>
  </div>
</div>

<div class="card welcome-card mb-4">
  <div class="card-header">
    <h3 class="card-title mb-0">Добро пожаловать в Quokka</h3>
  </div>
  <div class="card-body">
    <p class="mb-0">
      Здесь вы управляете пользователями и доступом к мобильному приложению.
      <?php if ($canViewUsers): ?>
        Перейдите в раздел «Пользователи», чтобы просмотреть учётные записи.
      <?php else: ?>
        У вашей роли ограниченный доступ — доступен только этот дашборд.
      <?php endif; ?>
    </p>
  </div>
</div>

<div class="row">
  <?php if ($canViewUsers): ?>
  <div class="col-md-4 mb-3">
    <a href="<?= htmlspecialchars(Url::to('users')) ?>" class="quick-action">
      <span class="quick-action-icon"><i class="fas fa-users"></i></span>
      <span>
        <strong>Пользователи</strong>
        <span>Список учётных записей и ролей</span>
      </span>
    </a>
  </div>
  <?php endif; ?>

  <div class="col-md-4 mb-3">
    <a href="/doc.php" class="quick-action" target="_blank" rel="noopener">
      <span class="quick-action-icon violet"><i class="fas fa-book"></i></span>
      <span>
        <strong>Документация</strong>
        <span>API и инструкции для разработчиков</span>
      </span>
    </a>
  </div>

  <div class="col-md-4 mb-3">
    <a href="/" class="quick-action" target="_blank" rel="noopener">
      <span class="quick-action-icon green"><i class="fas fa-globe"></i></span>
      <span>
        <strong>Главный сайт</strong>
        <span>Открыть публичную страницу</span>
      </span>
    </a>
  </div>
</div>

<?php if ($canDelete): ?>
<div class="callout callout-info mt-2">
  <h5><i class="fas fa-info-circle mr-1"></i> Права администратора</h5>
  <p class="mb-0">Вы можете удалять пользователей в разделе «Пользователи».</p>
</div>
<?php endif; ?>
