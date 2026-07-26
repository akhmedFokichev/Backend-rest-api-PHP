/**
 * store.js — глобальный AppStore админки Quokka.
 *
 * Назначение: route, user, права, навигация без reload;
 * подгружает HTML fragment + screen viewmodel.js для текущего экрана.
 * Логика конкретного экрана — в admin/views/screens/<name>/viewmodel.js
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
    viewmodels: {
      dashboard: '/admin/screens/dashboard/viewmodel.js',
      users: '/admin/screens/users/viewmodel.js',
    },

    contentLoading: false,
    contentError: null,
    contentRequestId: 0,
    loadedScripts: {},

    initFromBoot(boot) {
      this.user = boot.user || null;
      this.canViewUsers = !!boot.canViewUsers;
      this.canDelete = !!boot.canDelete;
      this.paths = Object.assign(this.paths, boot.paths || {});
      this.fragments = Object.assign(this.fragments, boot.fragments || {});
      this.viewmodels = Object.assign(this.viewmodels, boot.viewmodels || {});
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
        this.mountScreen(route);
        return;
      }
      this.setRoute(route, true);
      this.loadContent(route);
    },

    loadScript(url) {
      if (this.loadedScripts[url]) {
        return Promise.resolve();
      }

      return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = url;
        script.async = false;
        script.onload = () => {
          this.loadedScripts[url] = true;
          resolve();
        };
        script.onerror = () => reject(new Error('Не удалось загрузить viewmodel: ' + url));
        document.head.appendChild(script);
      });
    },

    async mountScreen(route) {
      const vm = window.QuokkaScreenVms && window.QuokkaScreenVms[route];
      if (vm && typeof vm.mount === 'function') {
        await vm.mount();
      }
    },

    async loadContent(route) {
      const el = document.getElementById('app-content');
      if (!el) return;

      const fragmentUrl = this.fragments[route];
      const viewmodelUrl = this.viewmodels[route];
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

        if (viewmodelUrl) {
          await this.loadScript(viewmodelUrl);
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

        await this.mountScreen(route);

        el.innerHTML = html;

        if (window.Alpine && typeof Alpine.initTree === 'function') {
          Alpine.initTree(el);
        }
      } catch (err) {
        if (requestId !== this.contentRequestId) {
          return;
        }
        this.contentError = err.message || 'Ошибка загрузки экрана';
        el.innerHTML = '<div class="alert alert-danger">' + this.contentError + '</div>';
      } finally {
        if (requestId === this.contentRequestId) {
          this.contentLoading = false;
        }
      }
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
