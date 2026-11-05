<?php

return [
    'app' => [
        'name'        => env('APP_NAME', 'TRIVEX'),
        'env'         => env('APP_ENV', 'local'),
        'debug'       => filter_var(env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN),
        'timezone'    => env('APP_TIMEZONE', 'Europe/Lisbon'),
        'base_path'   => null, // auto-detected by bootstrap
        'origin'      => env('APP_URL', ''),
    ],
    'db' => [
        'host'     => env('DB_HOST', '127.0.0.1'),
        'port'     => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'trivex'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
    ],
    'mail' => [
        'driver'     => env('MAIL_DRIVER', 'log'),
        'host'       => env('MAIL_HOST', '127.0.0.1'),
        'port'       => (int) env('MAIL_PORT', 1025),
        'username'   => env('MAIL_USERNAME', ''),
        'password'   => env('MAIL_PASSWORD', ''),
        'encryption' => env('MAIL_ENCRYPTION', ''),
        'from'       => env('MAIL_FROM') ?: (env('MAIL_USERNAME') ?: 'hello@trivex.test'),
        'from_name'  => env('MAIL_FROM_NAME', 'TRIVEX'),
    ],
    'locales' => ['pt', 'en'],
    'currency' => env('CURRENCY', 'EUR'),
];
