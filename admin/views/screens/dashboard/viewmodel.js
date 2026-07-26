/**
 * viewmodel.js — ViewModel экрана «Главная» (dashboard).
 *
 * Назначение: загрузка и хранение статистики для cards на dashboard.
 * Рядом с: admin/views/screens/dashboard/view.php
 * URL: /admin/screens/dashboard/viewmodel.js
 */
(function () {
  const STORE_NAME = 'dashboard';

  function createDashboardStore() {
    return {
      stats: {
        usersTotal: null,
        usersAdmin: null,
        usersModerator: null,
        usersRegular: null,
        apiOk: null,
      },
      loading: false,
      error: null,

      async mount() {
        await this.load();
      },

      async load() {
        const app = Alpine.store('app');
        this.loading = true;
        this.error = null;

        try {
          const dbCheck = await Api.get('db-check');
          this.stats.apiOk = !!(dbCheck && dbCheck.ok);

          if (!app.canViewUsers) {
            this.stats.usersTotal = null;
            this.stats.usersAdmin = null;
            this.stats.usersModerator = null;
            this.stats.usersRegular = null;
            return;
          }

          const response = await Api.get('user/list');
          const items = Array.isArray(response.items) ? response.items : [];

          let usersAdmin = 0;
          let usersModerator = 0;
          let usersRegular = 0;

          items.forEach((item) => {
            const role = Number(item.role || 0);
            if (role >= 100) usersAdmin += 1;
            else if (role >= 50) usersModerator += 1;
            else if (role >= 10) usersRegular += 1;
          });

          this.stats.usersTotal = items.length;
          this.stats.usersAdmin = usersAdmin;
          this.stats.usersModerator = usersModerator;
          this.stats.usersRegular = usersRegular;
        } catch (err) {
          this.error = err.message || 'Не удалось загрузить статистику';
          this.stats.apiOk = false;
        } finally {
          this.loading = false;
        }
      },

      formatStat(value) {
        const app = Alpine.store('app');
        if (!app.canViewUsers) return '—';
        return value === null || value === undefined ? '—' : String(value);
      },
    };
  }

  function register() {
    if (!window.Alpine) return;
    Alpine.store(STORE_NAME, createDashboardStore());
  }

  window.QuokkaScreenVms = window.QuokkaScreenVms || {};
  window.QuokkaScreenVms.dashboard = {
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
