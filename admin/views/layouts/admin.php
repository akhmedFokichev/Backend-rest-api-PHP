<?php

/**
 * admin.php — основной layout админки (sidebar, navbar, Alpine AppStore).
 *
 * Назначение: оболочка AdminLTE; контент переключается реактивно без reload.
 */

use App\Core\Auth;
use App\Core\Url;

$appConfig = require BASE_PATH . '/app/Config/app.php';
$apiConfig = require BASE_PATH . '/app/Config/api.php';
$user = Auth::user();

$boot = $boot ?? [
    'route' => $initialRoute ?? 'dashboard',
    'user' => $user,
    'canViewUsers' => Auth::can('users.view'),
    'canDelete' => Auth::can('*'),
    'paths' => [
        'dashboard' => Url::to(),
        'users' => Url::to('users'),
    ],
];
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
  <link rel="stylesheet" href="<?= htmlspecialchars(Url::asset('css/app.css')) ?>">
</head>
<body class="hold-transition sidebar-mini<?= ($apiConfig['mock_enabled'] ?? false) ? ' mock-mode' : '' ?>"
      x-data
      x-cloak>
<script type="application/json" id="app-boot"><?= json_encode($boot, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) ?></script>

<div class="wrapper">
  <?php require BASE_PATH . '/views/partials/navbar.php'; ?>
  <?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 x-text="$store.app.pageTitle"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
            <p class="page-subtitle mb-0" x-text="$store.app.pageSubtitle"><?= htmlspecialchars($pageSubtitle ?? '') ?></p>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right breadcrumb-bar">
              <li class="breadcrumb-item">
                <a href="<?= htmlspecialchars(Url::to()) ?>" @click.prevent="$store.app.navigate('dashboard')">Главная</a>
              </li>
              <li class="breadcrumb-item active"
                  x-show="$store.app.route !== 'dashboard'"
                  x-text="$store.app.pageTitle"
                  x-cloak></li>
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
<script src="<?= htmlspecialchars(Url::asset('js/api.js')) ?>"></script>
<script src="<?= htmlspecialchars(Url::asset('js/store.js')) ?>"></script>
<script src="<?= htmlspecialchars(Url::asset('js/app.js')) ?>"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
</body>
</html>
