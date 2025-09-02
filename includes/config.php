<?php

require_once __DIR__ . '/load_env.php';
loadEnv(__DIR__ . '/.env'); // Load the .env file

return [
    'servername' => $_ENV['DB_HOST'],
    'username' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASS'],
    'dbname'   => $_ENV['DB_NAME'],

    // SMTP Configuration
    'smtp_host'   => $_ENV['SMTP_HOST'],
    'smtp_user'   => $_ENV['SMTP_USER'],
    'smtp_pass'   => $_ENV['SMTP_PASS'],
    'smtp_secure' => $_ENV['SMTP_SECURE'],
    'smtp_port'   => $_ENV['SMTP_PORT'],
];
