<?php
/**
 * shell.php — контейнер контента SPA-админки.
 *
 * Назначение: пустой #app-content; HTML экрана подгружается через fragment при навигации.
 */
?>
<div id="app-content-wrap">
  <div class="users-loading" x-show="$store.app.contentLoading" x-cloak>
    <div class="spinner-border text-primary" role="status"></div>
    <div>Загрузка экрана…</div>
  </div>
  <div id="app-content" :class="{ 'd-none': $store.app.contentLoading }"></div>
</div>
