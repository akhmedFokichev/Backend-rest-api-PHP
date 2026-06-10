document.addEventListener('DOMContentLoaded', async () => {
  const table = document.getElementById('users-table');
  const tbody = table.querySelector('tbody');
  const alertBox = document.getElementById('users-alert');
  const canDelete = window.USERS_PAGE?.canDelete === true;

  function showError(message) {
    alertBox.textContent = message;
    alertBox.classList.remove('d-none');
  }

  try {
    const response = await Api.get('user/list');
    const users = response.items || [];

    tbody.innerHTML = users.map((u) => `
      <tr>
        <td>${u.id}</td>
        <td>${escapeHtml(u.login)}</td>
        <td><span class="badge badge-info">${escapeHtml(u.roleLabel || u.role)}</span></td>
        ${canDelete ? `
        <td>
          <button type="button" class="btn btn-xs btn-danger btn-delete" data-id="${u.id}">
            <i class="fas fa-trash"></i>
          </button>
        </td>` : ''}
      </tr>
    `).join('');

    $(table).DataTable({
      language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/ru.json' },
    });

    if (canDelete) {
      table.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-delete');
        if (!btn) return;

        const id = btn.dataset.id;
        if (!confirm('Удалить пользователя #' + id + '?')) return;

        try {
          await Api.delete('user/' + id);
          btn.closest('tr').remove();
        } catch (err) {
          showError(err.message || 'Ошибка удаления');
        }
      });
    }
  } catch (err) {
    showError(err.message || 'Не удалось загрузить пользователей');
  }
});
