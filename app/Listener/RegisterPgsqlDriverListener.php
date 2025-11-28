<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Contract\ContainerInterface;
use Hyperf\Database\Connectors\PostgresConnector;
use Hyperf\DbConnection\ConnectionFactory;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainWorkerStart;

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
        // Garante que rode no início do boot da aplicação, antes de qualquer processo.
        return [
            \Hyperf\Framework\Event\BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        // Obtém a factory responsável por criar as conexões
        $factory = $this->container->get(ConnectionFactory::class);
        
        // Força o registro explícito do conector 'pgsql'
        // Verifica se o método hasConnector existe para evitar erros em versões diferentes
        if (method_exists($factory, 'hasConnector') && !$factory->hasConnector('pgsql')) {
             $factory->extend('pgsql', function() {
                return new PostgresConnector();
            });
        } else {
            // Fallback para o método register se extend/hasConnector não funcionarem como esperado
            // ou se o driver já estiver registrado mas precisarmos garantir
             try {
                $factory->register('pgsql', new PostgresConnector());
             } catch (\Throwable $e) {
                // Ignora se já estiver registrado
             }
        }
    }
}
