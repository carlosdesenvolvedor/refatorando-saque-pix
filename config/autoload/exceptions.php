<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'handler' => [
        'http' => [
            // Adicione seu handler customizado aqui, no topo
            \App\Exception\Handler\BusinessExceptionHandler::class,
            \App\Exception\Handler\HttpNotFoundExceptionHandler::class,
            
            // Handler padrão do Hyperf
            \Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler::class,
            \App\Exception\Handler\AppExceptionHandler::class,
        ],
    ],
];
