<?php
/**
 * users/index.php — таблица пользователей.
 *
 * Назначение: HTML-скелет списка; данные подгружает assets/js/pages/users.js через API proxy.
 */

use App\Core\Url;
?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title mb-0"><i class="fas fa-users mr-2 text-primary"></i>Пользователи</h3>
  </div>
  <div class="card-body">
    <div class="users-toolbar">
      <p class="text-muted mb-0">Все зарегистрированные учётные записи платформы</p>
      <button type="button" class="btn btn-sm btn-outline-primary" id="users-refresh" disabled>
        <i class="fas fa-sync-alt"></i> Обновить
      </button>
    </div>

    <div id="users-alert" class="alert alert-danger d-none"></div>

    <div id="users-loading" class="users-loading">
      <div class="spinner-border text-primary" role="status"></div>
      <div>Загрузка списка…</div>
    </div>

    <div id="users-table-wrap" class="d-none">
      <table id="users-table" class="table table-hover table-users w-100">
        <thead>
          <tr>
            <th width="70">ID</th>
            <th>Логин</th>
            <th>Роль</th>
            <?php if (!empty($canDelete)): ?>
            <th width="90" class="text-center">Действия</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<script>
  window.USERS_PAGE = { canDelete: <?= !empty($canDelete) ? 'true' : 'false' ?> };
</script>
<script src="<?= htmlspecialchars(Url::asset('js/pages/users.js')) ?>"></script>
