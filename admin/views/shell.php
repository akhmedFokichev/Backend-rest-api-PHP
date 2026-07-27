<?php
/**
 * shell.php — контейнер контента SPA-админки.
 *
 * Назначение: пустой #app-content; HTML + viewmodel экрана подгружаются при навигации.
 * JS:
 *   - store.js — loadContent() (fragment HTML + screens/<name>/viewmodel.js)
 *   - app.js — boot AppStore
 * Экраны: admin/views/screens/<name>/{view.php, viewmodel.js}
 *
 * Важно: Alpine-директивы на #app-content-host, а не на #app-content —
 * иначе destroyTree снимает d-none и мелькает старый экран.
 */
?>
<div id="app-content-wrap">
  <div class="users-loading" x-show="$store.app.contentLoading" x-cloak>
    <div class="spinner-border text-primary" role="status"></div>
    <div>Загрузка экрана…</div>
  </div>
  <div id="app-content-host" :class="{ 'd-none': $store.app.contentLoading }">
    <div id="app-content"></div>
  </div>
</div>
