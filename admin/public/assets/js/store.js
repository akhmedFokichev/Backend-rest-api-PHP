/**
 * store.js — Alpine AppStore админки Quokka.
 *
 * Назначение: состояние экрана + навигация без reload;
 * HTML content подгружается фрагментами (/admin/fragment/*) при каждой смене route.
 */
document.addEventListener('alpine:init', () => {
  Alpine.store('app', {
    route: 'dashboard',
    pageTitle: 'Главная',
    pageSubtitle: 'Статистика платформы',
    documentTitle: 'Главная — Quokka Admin',

    user: null,
    canViewUsers: false,
    canDelete: false,
    paths: {
      dashboard: '/admin',
      users: '/admin/users',
    },
    fragments: {
      dashboard: '/admin/fragment/dashboard',
      users: '/admin/fragment/users',
    },

    contentLoading: false,
    contentError: null,
    contentRequestId: 0,

    stats: {
      usersTotal: null,
      usersAdmin: null,
      usersModerator: null,
      usersRegular: null,
      apiOk: null,
    },
    statsLoading: false,
    statsError: null,

    users: [],
    usersLoading: false,
    usersError: null,
    usersLoaded: false,

    initFromBoot(boot) {
      this.user = boot.user || null;
      this.canViewUsers = !!boot.canViewUsers;
      this.canDelete = !!boot.canDelete;
      this.paths = Object.assign(this.paths, boot.paths || {});
      this.fragments = Object.assign(this.fragments, boot.fragments || {});
      const route = boot.route || this.routeFromPath(window.location.pathname);
      this.setRoute(route, false);
      this.loadContent(route);
    },

    routeFromPath(pathname) {
      const path = (pathname || '').replace(/\/+$/, '') || '/';
      const users = (this.paths.users || '/admin/users').replace(/\/+$/, '');
      if (path === users || path.startsWith(users + '/')) {
        return 'users';
      }
      return 'dashboard';
    },

    setRoute(route, push = true) {
      if (route === 'users' && !this.canViewUsers) {
        route = 'dashboard';
      }

      this.route = route;

      if (route === 'users') {
        this.pageTitle = 'Пользователи';
        this.pageSubtitle = 'Управление учётными записями';
        this.documentTitle = 'Пользователи — Quokka Admin';
      } else {
        this.pageTitle = 'Главная';
        this.pageSubtitle = 'Статистика платформы';
        this.documentTitle = 'Главная — Quokka Admin';
      }

      document.title = this.documentTitle;

      const url = this.paths[route] || this.paths.dashboard;
      if (push) {
        history.pushState({ route }, this.documentTitle, url);
      } else {
        history.replaceState({ route }, this.documentTitle, url);
      }
    },

    navigate(route) {
      if (this.route === route && document.getElementById('app-content')?.innerHTML.trim()) {
        this.ensureData();
        return;
      }
      this.setRoute(route, true);
      this.loadContent(route);
    },

    async loadContent(route) {
      const el = document.getElementById('app-content');
      if (!el) return;

      const fragmentUrl = this.fragments[route];
      if (!fragmentUrl) {
        this.contentError = 'Неизвестный экран';
        return;
      }

      const requestId = ++this.contentRequestId;
      this.contentLoading = true;
      this.contentError = null;

      try {
        if (window.Alpine && typeof Alpine.destroyTree === 'function') {
          Alpine.destroyTree(el);
        }

        const response = await fetch(fragmentUrl, {
          headers: {
            Accept: 'text/html',
            'X-Requested-With': 'XMLHttpRequest',
          },
          credentials: 'same-origin',
        });

        if (requestId !== this.contentRequestId) {
          return;
        }

        if (response.status === 401) {
          window.location.href = '/admin/login';
          return;
        }

        if (response.status === 403) {
          this.contentError = 'Недостаточно прав для этого экрана';
          el.innerHTML = '<div class="alert alert-warning">Недостаточно прав</div>';
          return;
        }

        if (!response.ok) {
          throw new Error('Не удалось загрузить экран');
        }

        const html = await response.text();
        if (requestId !== this.contentRequestId) {
          return;
        }

        el.innerHTML = html;

        if (window.Alpine && typeof Alpine.initTree === 'function') {
          Alpine.initTree(el);
        }

        await this.ensureData();
      } catch (err) {
        if (requestId !== this.contentRequestId) {
          return;
        }
        this.contentError = err.message || 'Ошибка загрузки экрана';
        el.innerHTML = '<div class="alert alert-danger">' + (this.contentError) + '</div>';
      } finally {
        if (requestId === this.contentRequestId) {
          this.contentLoading = false;
        }
      }
    },

    ensureData() {
      this.loadStats();
      if (this.route === 'users') {
        this.loadUsers(true);
      }
    },

    async loadStats() {
      this.statsLoading = true;
      this.statsError = null;

      try {
        const dbCheck = await Api.get('db-check');
        this.stats.apiOk = !!(dbCheck && dbCheck.ok);

        if (!this.canViewUsers) {
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

        this.users = items;
        this.usersLoaded = true;
      } catch (err) {
        this.statsError = err.message || 'Не удалось загрузить статистику';
        this.stats.apiOk = false;
      } finally {
        this.statsLoading = false;
      }
    },

    async loadUsers(force = false) {
      if (!this.canViewUsers) return;
      if (this.usersLoading) return;
      if (this.usersLoaded && !force) return;

      this.usersLoading = true;
      this.usersError = null;

      try {
        const response = await Api.get('user/list');
        this.users = Array.isArray(response.items) ? response.items : [];
        this.usersLoaded = true;
      } catch (err) {
        this.usersError = err.message || 'Не удалось загрузить пользователей';
      } finally {
        this.usersLoading = false;
      }
    },

    async refreshUsers() {
      this.usersLoaded = false;
      await this.loadUsers(true);
      await this.loadStats();
    },

    async deleteUser(id) {
      if (!this.canDelete) return;
      if (!confirm('Удалить пользователя #' + id + '?')) return;

      try {
        await Api.delete('user/' + id);
        this.users = this.users.filter((u) => String(u.id) !== String(id));
        await this.loadStats();
      } catch (err) {
        this.usersError = err.message || 'Ошибка удаления';
      }
    },

    roleBadgeClass(roleLabel) {
      const label = String(roleLabel || '').toLowerCase();
      if (label.includes('admin') || label.includes('админ')) return 'role-badge-admin';
      if (label.includes('moder') || label.includes('модер')) return 'role-badge-moderator';
      if (label.includes('guest') || label.includes('гост')) return 'role-badge-guest';
      return 'role-badge-user';
    },

    formatStat(value) {
      if (!this.canViewUsers) return '—';
      return value === null || value === undefined ? '—' : String(value);
    },
  });
});

window.addEventListener('popstate', (event) => {
  const store = window.Alpine && Alpine.store('app');
  if (!store) return;

  const route = (event.state && event.state.route) || store.routeFromPath(window.location.pathname);
  store.setRoute(route, false);
  store.loadContent(route);
});
