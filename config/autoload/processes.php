<?php

declare(strict_types=1);

use Hyperf\AsyncQueue\Process\ConsumerProcess;
use Hyperf\Crontab\Process\CrontabDispatcherProcess;

return [
    CrontabDispatcherProcess::class, // OBRIGATÓRIO para o Cron
    ConsumerProcess::class,      // OBRIGATÓRIO para a Fila/Email
];