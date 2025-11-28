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
    Hyperf\Database\Commands\Migrations\MigrateCommand::class,
    Hyperf\Database\Commands\Migrations\InstallCommand::class,
    Hyperf\Database\Commands\Migrations\RollbackCommand::class,
    Hyperf\Database\Commands\Migrations\ResetCommand::class,
    Hyperf\Database\Commands\Migrations\RefreshCommand::class,
    Hyperf\Database\Commands\Migrations\FreshCommand::class,
    Hyperf\Database\Commands\Migrations\StatusCommand::class,
];
