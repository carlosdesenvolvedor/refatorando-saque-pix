<?php

declare(strict_types=1);

namespace App;

use App\Listener\RegisterPgsqlDriverListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                RegisterPgsqlDriverListener::class,
            ],
        ];
    }
}
