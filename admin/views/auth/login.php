<?php
/**
 * login.php — форма входа в админ-панель.
 *
 * Назначение: POST логин/пароль → AuthController::login → API user/login.
 */
?>
<div class="login-box">
  <div class="login-logo">
    <a href="/" class="brand-quokka">
      <span class="brand-quokka-mark">QK</span>
      <span class="brand-quokka-text">
        <strong>Quokka</strong>
        <span>Панель управления</span>
      </span>
    </a>
  </div>
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Войдите с учётной записью модератора или администратора</p>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="<?= htmlspecialchars(\App\Core\Url::to('login')) ?>" method="post">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
        <div class="input-group mb-3">
          <input type="text" name="login" class="form-control" placeholder="Логин или email" required
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
            <button type="submit" class="btn btn-primary btn-block">
              <i class="fas fa-sign-in-alt mr-1"></i> Войти
            </button>
          </div>
        </div>
      </form>

      <div class="login-footer">
        <a href="/">← На главную страницу</a>
      </div>
    </div>
  </div>
</div>
