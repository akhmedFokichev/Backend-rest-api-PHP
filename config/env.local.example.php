<?php

// Copy this file to env.local.php and fill real values. Do NOT commit env.local.php
return [
    'APP_NAME'   => 'Puma',
    'APP_VERSION'=> '0.0.1',

    // Identity / JWT
    'HASH_KEY'   => 'change-me',
    'CLIENT_IDS' => 'web_app', // comma-separated
    'JWT_SECRET' => 'change-me-secret',

    // Database
    'DB_HOST'    => 'localhost',
    'DB_NAME'    => '',
    'DB_USER'    => '',
    'DB_PASS'    => '',
];


