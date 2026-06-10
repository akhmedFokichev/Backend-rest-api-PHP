<div class="card">
  <div class="card-header">
    <h3 class="card-title">Пользователи</h3>
    <div class="card-tools text-muted small">
      GET /api/v1/user/list
    </div>
  </div>
  <div class="card-body">
    <div id="users-alert" class="alert alert-danger d-none"></div>
    <table id="users-table" class="table table-bordered table-striped w-100">
      <thead>
        <tr>
          <th>ID</th>
          <th>Логин</th>
          <th>Роль</th>
          <?php if (!empty($canDelete)): ?>
          <th width="80">Действия</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<script>
  window.USERS_PAGE = { canDelete: <?= !empty($canDelete) ? 'true' : 'false' ?> };
</script>
<script src="/assets/js/pages/users.js"></script>
