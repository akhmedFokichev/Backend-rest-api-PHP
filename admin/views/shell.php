<?php
/**
 * shell.php — контейнер контента SPA-админки.
 *
 * Назначение: пустой #app-content; HTML + viewmodel экрана подгружаются при навигации.
 * JS:
 *   - store.js — loadContent() (fragment HTML + screens/<name>/viewmodel.js)
 *   - app.js — boot AppStore
 * Экраны: admin/views/screens/<name>/{view.php, viewmodel.js}
 */
?>
<div id="app-content-wrap">
  <div class="users-loading" x-show="$store.app.contentLoading" x-cloak>
    <div class="spinner-border text-primary" role="status"></div>
    <div>Загрузка экрана…</div>
  </div>
  <div id="app-content" :class="{ 'd-none': $store.app.contentLoading }"></div>
</div>
