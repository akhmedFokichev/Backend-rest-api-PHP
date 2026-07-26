<?php
/**
 * dashboard.php — HTML-фрагмент экрана «Главная».
 *
 * Назначение: карточки статистики; подгружается в #app-content при навигации.
 */
?>
<div class="screen-dashboard">
  <div class="row">
    <div class="col-lg-3 col-md-6 mb-3">
      <template x-if="$store.app.canViewUsers">
        <a href="<?= htmlspecialchars(\App\Core\Url::to('users')) ?>"
           class="stat-card-link"
           @click.prevent="$store.app.navigate('users')">
          <div class="small-box stat-card bg-gradient-primary">
            <div class="inner">
              <h3 x-text="$store.app.formatStat($store.app.stats.usersTotal)">—</h3>
              <p>Пользователей</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <span class="small-box-footer">Перейти к списку <i class="fas fa-arrow-circle-right"></i></span>
          </div>
        </a>
      </template>
      <template x-if="!$store.app.canViewUsers">
        <div class="small-box stat-card bg-gradient-primary">
          <div class="inner">
            <h3>—</h3>
            <p>Пользователей</p>
          </div>
          <div class="icon"><i class="fas fa-users"></i></div>
        </div>
      </template>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
      <div class="small-box stat-card bg-gradient-danger">
        <div class="inner">
          <h3 x-text="$store.app.formatStat($store.app.stats.usersAdmin)">—</h3>
          <p>Администраторов</p>
        </div>
        <div class="icon"><i class="fas fa-user-shield"></i></div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
      <div class="small-box stat-card bg-gradient-violet">
        <div class="inner">
          <h3 x-text="$store.app.formatStat($store.app.stats.usersModerator)">—</h3>
          <p>Модераторов</p>
        </div>
        <div class="icon"><i class="fas fa-user-cog"></i></div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
      <div class="small-box stat-card bg-gradient-info">
        <div class="inner">
          <h3 x-text="$store.app.formatStat($store.app.stats.usersRegular)">—</h3>
          <p>Обычных пользователей</p>
        </div>
        <div class="icon"><i class="fas fa-user"></i></div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-4 col-md-6 mb-3">
      <div class="small-box stat-card"
           :class="$store.app.stats.apiOk ? 'bg-gradient-success' : 'bg-gradient-warning'">
        <div class="inner">
          <h3 x-text="$store.app.stats.apiOk ? 'OK' : '—'">—</h3>
          <p x-text="$store.app.stats.apiOk ? 'API и база данных' : 'API недоступен'">API</p>
        </div>
        <div class="icon"><i class="fas fa-database"></i></div>
      </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
      <div class="small-box stat-card bg-gradient-secondary">
        <div class="inner">
          <h3 x-text="$store.app.user?.roleLabel || '—'">—</h3>
          <p>Ваша роль</p>
        </div>
        <div class="icon"><i class="fas fa-id-badge"></i></div>
      </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
      <div class="small-box stat-card bg-gradient-dark">
        <div class="inner">
          <h3 x-text="$store.app.user?.login || '—'">—</h3>
          <p>Текущий аккаунт</p>
        </div>
        <div class="icon"><i class="fas fa-user-circle"></i></div>
      </div>
    </div>
  </div>

  <div class="callout callout-warning" x-show="!$store.app.canViewUsers" x-cloak>
    <p class="mb-0">
      <i class="fas fa-lock mr-1"></i>
      Статистика пользователей доступна модераторам и администраторам.
    </p>
  </div>

  <div class="alert alert-danger" x-show="$store.app.statsError" x-text="$store.app.statsError" x-cloak></div>
</div>
