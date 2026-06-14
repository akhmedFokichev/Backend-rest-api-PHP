<?php

/**
 * admin.php — основной layout админки (sidebar, navbar, скрипты).
 *
 * Назначение: обёртка для всех авторизованных страниц AdminLTE.
 */

use App\Core\Auth;
use App\Core\Url;

$appConfig = require BASE_PATH . '/app/Config/app.php';
$apiConfig = require BASE_PATH . '/app/Config/api.php';
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= htmlspecialchars(Auth::csrfToken()) ?>">
  <title><?= htmlspecialchars($title ?? $appConfig['name']) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.8/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="<?= htmlspecialchars(Url::asset('css/app.css')) ?>">
</head>
<body class="hold-transition sidebar-mini<?= ($apiConfig['mock_enabled'] ?? false) ? ' mock-mode' : '' ?>">
<div class="wrapper">
  <?php require BASE_PATH . '/views/partials/navbar.php'; ?>
  <?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1><?= htmlspecialchars($pageTitle ?? '') ?></h1>
            <?php if (!empty($pageSubtitle)): ?>
              <p class="page-subtitle mb-0"><?= htmlspecialchars($pageSubtitle) ?></p>
            <?php endif; ?>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right breadcrumb-bar">
              <li class="breadcrumb-item"><a href="<?= htmlspecialchars(Url::to()) ?>">Главная</a></li>
              <?php if (!empty($pageTitle) && ($pageTitle ?? '') !== 'Главная'): ?>
              <li class="breadcrumb-item active"><?= htmlspecialchars($pageTitle) ?></li>
              <?php endif; ?>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <?php require BASE_PATH . '/views/partials/alerts.php'; ?>
        <?php require $viewFile; ?>
      </div>
    </section>
  </div>

  <?php require BASE_PATH . '/views/partials/footer.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= htmlspecialchars(Url::asset('js/api.js')) ?>"></script>
<script src="<?= htmlspecialchars(Url::asset('js/app.js')) ?>"></script>
</body>
</html>
