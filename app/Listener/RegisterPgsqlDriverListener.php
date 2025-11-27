<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Database\Connectors\PostgresConnector;
use Hyperf\DbConnection\ConnectionFactory;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeServerStart;
use Psr\Container\ContainerInterface;

#[Listener]
class RegisterPgsqlDriverListener implements ListenerInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            BeforeServerStart::class,
        ];
    }

    public function process(object $event): void
    {
        // Força o registro do driver pgsql no sistema de conexões
        $factory = $this->container->get(ConnectionFactory::class);
        $factory->register('pgsql', new PostgresConnector());
    }
}
