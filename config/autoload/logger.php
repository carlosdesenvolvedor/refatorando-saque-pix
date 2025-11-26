<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Handler\SocketHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;

return [
    'default' => [
        'handlers' => [
            // Handler 1: Output to Docker Console (Human Readable)
            [
                'class' => StreamHandler::class,
                'constructor' => [
                    'stream' => 'php://stdout',
                    'level' => Logger::INFO,
                ],
                'formatter' => [
                    'class' => LineFormatter::class,
                    'constructor' => [
                        'format' => null,
                        'dateFormat' => 'Y-m-d H:i:s',
                        'allowInlineLineBreaks' => true,
                    ],
                ],
            ],
            // Handler 2: Output to Fluentd (Machine Readable)
            [
                'class' => SocketHandler::class,
                'constructor' => [
                    'connectionString' => 'tcp://saque-pix-fluentd:24224',
                    'level' => Logger::INFO,
                ],
                'formatter' => [
                    'class' => JsonFormatter::class,
                ],
            ],
        ],
    ],
];
