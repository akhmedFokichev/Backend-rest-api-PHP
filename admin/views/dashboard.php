<?php
/**
 * dashboard.php — главная страница админки после входа.
 *
 * Назначение: карточки со статистикой платформы.
 */

use App\Core\Url;

$stats = $stats ?? [];
$canViewUsers = $canViewUsers ?? false;

function statValue(?int $value, bool $canView): string
{
    if (!$canView) {
        return '—';
    }

    return $value === null ? '—' : (string) $value;
}
?>
<div class="row">
  <div class="col-lg-3 col-md-6 mb-3">
    <?php if ($canViewUsers): ?>
    <a href="<?= htmlspecialchars(Url::to('users')) ?>" class="stat-card-link">
    <?php endif; ?>
      <div class="small-box stat-card bg-gradient-primary">
        <div class="inner">
          <h3><?= statValue($stats['usersTotal'] ?? null, $canViewUsers) ?></h3>
          <p>Пользователей</p>
        </div>
        <div class="icon"><i class="fas fa-users"></i></div>
        <?php if ($canViewUsers): ?>
        <span class="small-box-footer">Перейти к списку <i class="fas fa-arrow-circle-right"></i></span>
        <?php endif; ?>
      </div>
    <?php if ($canViewUsers): ?>
    </a>
    <?php endif; ?>
  </div>

  <div class="col-lg-3 col-md-6 mb-3">
    <div class="small-box stat-card bg-gradient-danger">
      <div class="inner">
        <h3><?= statValue($stats['usersAdmin'] ?? null, $canViewUsers) ?></h3>
        <p>Администраторов</p>
      </div>
      <div class="icon"><i class="fas fa-user-shield"></i></div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-3">
    <div class="small-box stat-card bg-gradient-violet">
      <div class="inner">
        <h3><?= statValue($stats['usersModerator'] ?? null, $canViewUsers) ?></h3>
        <p>Модераторов</p>
      </div>
      <div class="icon"><i class="fas fa-user-cog"></i></div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-3">
    <div class="small-box stat-card bg-gradient-info">
      <div class="inner">
        <h3><?= statValue($stats['usersRegular'] ?? null, $canViewUsers) ?></h3>
        <p>Обычных пользователей</p>
      </div>
      <div class="icon"><i class="fas fa-user"></i></div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-4 col-md-6 mb-3">
    <div class="small-box stat-card <?= ($stats['apiOk'] ?? false) ? 'bg-gradient-success' : 'bg-gradient-warning' ?>">
      <div class="inner">
        <h3><?= ($stats['apiOk'] ?? false) ? 'OK' : '—' ?></h3>
        <p><?= ($stats['apiOk'] ?? false) ? 'API и база данных' : 'API недоступен' ?></p>
      </div>
      <div class="icon"><i class="fas fa-database"></i></div>
    </div>
  </div>

  <div class="col-lg-4 col-md-6 mb-3">
    <div class="small-box stat-card bg-gradient-secondary">
      <div class="inner">
        <h3><?= htmlspecialchars($user['roleLabel'] ?? '—') ?></h3>
        <p>Ваша роль</p>
      </div>
      <div class="icon"><i class="fas fa-id-badge"></i></div>
    </div>
  </div>

  <div class="col-lg-4 col-md-6 mb-3">
    <div class="small-box stat-card bg-gradient-dark">
      <div class="inner">
        <h3><?= htmlspecialchars($user['login'] ?? '—') ?></h3>
        <p>Текущий аккаунт</p>
      </div>
      <div class="icon"><i class="fas fa-user-circle"></i></div>
    </div>
  </div>
</div>

<?php if (!$canViewUsers): ?>
<div class="callout callout-warning">
  <p class="mb-0">
    <i class="fas fa-lock mr-1"></i>
    Статистика пользователей доступна модераторам и администраторам.
  </p>
</div>
<?php endif; ?>
