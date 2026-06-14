/**
 * pages/users.js — логика страницы «Пользователи».
 *
 * Назначение: загрузка GET user/list, отрисовка таблицы, удаление (Admin).
 */
document.addEventListener('DOMContentLoaded', () => {
  const table = document.getElementById('users-table');
  const tbody = table.querySelector('tbody');
  const alertBox = document.getElementById('users-alert');
  const loadingBox = document.getElementById('users-loading');
  const tableWrap = document.getElementById('users-table-wrap');
  const refreshBtn = document.getElementById('users-refresh');
  const canDelete = window.USERS_PAGE?.canDelete === true;
  let dataTable = null;

  function showError(message) {
    alertBox.textContent = message;
    alertBox.classList.remove('d-none');
  }

  function hideError() {
    alertBox.classList.add('d-none');
    alertBox.textContent = '';
  }

  function roleBadgeClass(roleLabel) {
    const label = String(roleLabel || '').toLowerCase();
    if (label.includes('admin') || label.includes('админ')) return 'role-badge-admin';
    if (label.includes('moder') || label.includes('модер')) return 'role-badge-moderator';
    if (label.includes('guest') || label.includes('гост')) return 'role-badge-guest';
    return 'role-badge-user';
  }

  function setLoading(isLoading) {
    loadingBox.classList.toggle('d-none', !isLoading);
    tableWrap.classList.toggle('d-none', isLoading);
    if (refreshBtn) {
      refreshBtn.disabled = isLoading;
    }
  }

  async function loadUsers() {
    hideError();
    setLoading(true);

    if (dataTable) {
      dataTable.destroy();
      dataTable = null;
      tbody.innerHTML = '';
    }

    try {
      const response = await Api.get('user/list');
      const users = response.items || [];

      if (users.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="${canDelete ? 4 : 3}" class="text-center text-muted py-4">
              Пользователей пока нет
            </td>
          </tr>
        `;
      } else {
        tbody.innerHTML = users.map((u) => `
          <tr>
            <td>${u.id}</td>
            <td><strong>${escapeHtml(u.login)}</strong></td>
            <td>
              <span class="badge ${roleBadgeClass(u.roleLabel)}">${escapeHtml(u.roleLabel || u.role)}</span>
            </td>
            ${canDelete ? `
            <td class="text-center">
              <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="${u.id}" title="Удалить">
                <i class="fas fa-trash"></i>
              </button>
            </td>` : ''}
          </tr>
        `).join('');
      }

      setLoading(false);

      dataTable = $(table).DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/ru.json' },
        pageLength: 25,
        order: [[0, 'desc']],
      });
    } catch (err) {
      setLoading(false);
      showError(err.message || 'Не удалось загрузить пользователей');
    }
  }

  table.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-delete');
    if (!btn) return;

    const id = btn.dataset.id;
    if (!confirm('Удалить пользователя #' + id + '?')) return;

    btn.disabled = true;

    try {
      await Api.delete('user/' + id);
      await loadUsers();
    } catch (err) {
      btn.disabled = false;
      showError(err.message || 'Ошибка удаления');
    }
  });

  if (refreshBtn) {
    refreshBtn.addEventListener('click', loadUsers);
  }

  loadUsers();
});
