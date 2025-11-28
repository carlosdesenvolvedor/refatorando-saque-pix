<?php

declare(strict_types=1);

use function Hyperf\Support\env;

// DEBUG TEMPORÁRIO: Descomente para ver as variáveis no log do Render
// echo "DEBUG DB CONFIG:\n";
// var_dump([
//     'host' => env('DB_HOST'),
//     'port' => env('DB_PORT'),
//     'database' => env('DB_DATABASE'),
//     'username' => env('DB_USERNAME'),
//     'password_len' => strlen(env('DB_PASSWORD', '')),
// ]);
// die(); 

return [
    'default' => [
        'driver' => 'pdo-pgsql', // Alterado de 'pgsql' para 'pdo-pgsql' conforme solicitado
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 5432),
        'database' => env('DB_DATABASE', 'saque_pix_db'),
        'username' => env('DB_USERNAME', 'saque_pix_db_user'),
        'password' => env('DB_PASSWORD', 'sua_senha_default'),
        'charset' => 'utf8',
        'prefix' => '',
        'schema' => 'public', // Necessário para PostgreSQL
        'sslmode' => 'require', // Importante para conexões em nuvem (Render/AWS/etc)
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ],
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
