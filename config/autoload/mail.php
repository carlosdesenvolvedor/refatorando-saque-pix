<?php

declare(strict_types=1);

/**
 * Configuração do mail usando getenv() para leitura segura no bootstrap.
 */

$host = getenv('MAIL_HOST') ?: 'saque-pix-mailhog';
$port = (int) (getenv('MAIL_PORT') ?: 1025);
$fromAddress = getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@saquepix.local';
$fromName = getenv('MAIL_FROM_NAME') ?: 'SaquePix';

return [
    'default' => getenv('MAIL_DRIVER') ?: 'smtp',

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'scheme' => 'smtp', // Chave obrigatória para o DSN do mailer
            'host' => $host,
            'port' => $port,
            'encryption' => getenv('MAIL_ENCRYPTION') ?: null,
            'username' => getenv('MAIL_USERNAME') ?: null,
            'password' => getenv('MAIL_PASSWORD') ?: null,
            'timeout' => null,
            'auth_mode' => null,
            // Adicionado para compatibilidade com MailHog (ignora verificação SSL)
            'stream_options' => [
                'ssl' => [
                    'allow_self_signed' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ],
        ],
    ],

    'from' => [
        'address' => $fromAddress,
        'name' => $fromName,
    ],
];