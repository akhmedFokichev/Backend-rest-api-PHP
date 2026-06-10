<?php

/**
 * index.php — HTTP-вход API на хостинге.
 *
 * Назначение: проксирует запросы /api/* в api/bootstrap.php (Slim).
 * Document root: public_html; URL: /api/v1/...
 */

require __DIR__ . '/../../api/bootstrap.php';
