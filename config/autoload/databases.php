<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    'default' => [
        'driver' => 'pgsql', // Valor fixo para garantir que não haja erros de variável de ambiente
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 5432),
        'database' => env('DB_DATABASE', 'saque_pix_db'),
        'username' => env('DB_USERNAME', 'saque_pix_db_user'),
        'password' => env('DB_PASSWORD', 'sua_senha_default'),
        'charset' => 'utf8',
        'prefix' => '',
        'schema' => 'public', // Necessário para PostgreSQL
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => 60.0,
        ],
    ],
];
