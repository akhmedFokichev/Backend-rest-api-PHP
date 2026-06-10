<?php
/**
 * login.php — форма входа в админ-панель.
 *
 * Назначение: POST логин/пароль → AuthController::login → API user/login.
 */
?>
<div class="login-box">
  <div class="login-logo"><b>CMS</b> Admin</div>
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Войдите через Slim API</p>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="<?= htmlspecialchars(\App\Core\Url::to('login')) ?>" method="post">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
        <div class="input-group mb-3">
          <input type="text" name="login" class="form-control" placeholder="Логин" required
                 autocomplete="username"
                 value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
          <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-user"></span></div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Пароль" required
                 autocomplete="current-password">
          <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-lock"></span></div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Войти</button>
          </div>
        </div>
      </form>

      <p class="mt-3 mb-0 text-muted small">
        API: <code>POST /api/v1/user/login</code> с полями <code>login</code>, <code>password</code>.
      </p>
    </div>
  </div>
</div>
