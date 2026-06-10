<?php

$apiConfig = require BASE_PATH . '/app/Config/api.php';
if ($apiConfig['mock_enabled'] ?? false): ?>
<div class="alert alert-warning alert-dismissible">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <strong>Mock-режим.</strong> Данные демо. Подключите Slim API в <code>app/Config/api.php</code>.
</div>
<?php endif; ?>
