<?php

/**
 * guest.php — layout для гостевых страниц (логин, ошибки).
 *
 * Назначение: минимальная обёртка без sidebar.
 */

use App\Core\Url;

$appConfig = require BASE_PATH . '/app/Config/app.php';
$apiConfig = require BASE_PATH . '/app/Config/api.php';
$guestBodyClass = ($guestBodyClass ?? 'login-page quokka-login');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? $appConfig['name']) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="<?= htmlspecialchars(Url::asset('css/app.css')) ?>">
</head>
<body class="hold-transition <?= htmlspecialchars($guestBodyClass) ?><?= ($apiConfig['mock_enabled'] ?? false) ? ' mock-mode' : '' ?>">
<?php require $viewFile; ?>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars(Url::asset('js/api.js')) ?>"></script>
<script src="<?= htmlspecialchars(Url::asset('js/app.js')) ?>"></script>
</body>
</html>
