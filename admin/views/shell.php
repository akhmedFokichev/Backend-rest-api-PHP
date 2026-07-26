<?php
/**
 * shell.php — контент SPA-оболочки админки на Alpine.js.
 *
 * Назначение: переключает экраны (dashboard / users) без перезагрузки страницы.
 */
?>
<div>
  <?php require BASE_PATH . '/views/screens/dashboard.php'; ?>
  <?php require BASE_PATH . '/views/screens/users.php'; ?>
</div>
