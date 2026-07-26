/**
 * viewmodel.js — ViewModel экрана «Пользователи».
 *
 * Назначение: список пользователей, обновление и удаление.
 * Рядом с: admin/views/screens/users/view.php
 * URL: /admin/screens/users/viewmodel.js
 */
(function () {
  const STORE_NAME = 'users';

  function createUsersStore() {
    return {
      items: [],
      loading: false,
      error: null,
      loaded: false,

      async mount() {
        await this.load(true);
      },

      async load(force = false) {
        const app = Alpine.store('app');
        if (!app.canViewUsers) return;
        if (this.loading) return;
        if (this.loaded && !force) return;

        this.loading = true;
        this.error = null;

        try {
          const response = await Api.get('user/list');
          this.items = Array.isArray(response.items) ? response.items : [];
          this.loaded = true;
        } catch (err) {
          this.error = err.message || 'Не удалось загрузить пользователей';
        } finally {
          this.loading = false;
        }
      },

      async refresh() {
        this.loaded = false;
        await this.load(true);
      },

      async remove(id) {
        const app = Alpine.store('app');
        if (!app.canDelete) return;
        if (!confirm('Удалить пользователя #' + id + '?')) return;

        try {
          await Api.delete('user/' + id);
          this.items = this.items.filter((u) => String(u.id) !== String(id));
        } catch (err) {
          this.error = err.message || 'Ошибка удаления';
        }
      },

      roleBadgeClass(roleLabel) {
        const label = String(roleLabel || '').toLowerCase();
        if (label.includes('admin') || label.includes('админ')) return 'role-badge-admin';
        if (label.includes('moder') || label.includes('модер')) return 'role-badge-moderator';
        if (label.includes('guest') || label.includes('гост')) return 'role-badge-guest';
        return 'role-badge-user';
      },
    };
  }

  function register() {
    if (!window.Alpine) return;
    Alpine.store(STORE_NAME, createUsersStore());
  }

  window.QuokkaScreenVms = window.QuokkaScreenVms || {};
  window.QuokkaScreenVms.users = {
    register,
    async mount() {
      register();
      await Alpine.store(STORE_NAME).mount();
    },
  };

  if (window.Alpine) {
    register();
  } else {
    document.addEventListener('alpine:init', register);
  }
})();
