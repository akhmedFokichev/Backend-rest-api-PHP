<?php
/**
 * users.php — HTML-фрагмент экрана «Пользователи».
 *
 * Назначение: таблица users; подгружается в #app-content при навигации.
 */
?>
<div class="screen-users">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title mb-0"><i class="fas fa-users mr-2 text-primary"></i>Пользователи</h3>
    </div>
    <div class="card-body">
      <div class="users-toolbar">
        <p class="text-muted mb-0">Все зарегистрированные учётные записи платформы</p>
        <button type="button"
                class="btn btn-sm btn-outline-primary"
                :disabled="$store.app.usersLoading"
                @click="$store.app.refreshUsers()">
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': $store.app.usersLoading }"></i> Обновить
        </button>
      </div>

      <div class="alert alert-danger" x-show="$store.app.usersError" x-text="$store.app.usersError" x-cloak></div>

      <div class="users-loading" x-show="$store.app.usersLoading" x-cloak>
        <div class="spinner-border text-primary" role="status"></div>
        <div>Загрузка списка…</div>
      </div>

      <div x-show="!$store.app.usersLoading" x-cloak>
        <table class="table table-hover table-users w-100">
          <thead>
            <tr>
              <th width="70">ID</th>
              <th>Логин</th>
              <th>Роль</th>
              <th width="90" class="text-center" x-show="$store.app.canDelete">Действия</th>
            </tr>
          </thead>
          <tbody>
            <template x-if="$store.app.users.length === 0">
              <tr>
                <td :colspan="$store.app.canDelete ? 4 : 3" class="text-center text-muted py-4">
                  Пользователей пока нет
                </td>
              </tr>
            </template>
            <template x-for="u in $store.app.users" :key="u.id">
              <tr>
                <td x-text="u.id"></td>
                <td><strong x-text="u.login"></strong></td>
                <td>
                  <span class="badge" :class="$store.app.roleBadgeClass(u.roleLabel)" x-text="u.roleLabel || u.role"></span>
                </td>
                <td class="text-center" x-show="$store.app.canDelete">
                  <button type="button"
                          class="btn btn-sm btn-outline-danger"
                          title="Удалить"
                          @click="$store.app.deleteUser(u.id)">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
