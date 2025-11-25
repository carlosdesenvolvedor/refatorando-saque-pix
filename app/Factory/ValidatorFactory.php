<?php

declare(strict_types=1);

namespace App\Factory;

use Hyperf\Contract\ContainerInterface;
use Hyperf\Validation\DatabasePresenceVerifier;
use Psr\Http\Message\ResponseInterface;

class ValidatorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $validatorFactory = new \Hyperf\Validation\ValidatorFactory($container->get(\Hyperf\Contract\TranslatorInterface::class));
        $validatorFactory->setPresenceVerifier($container->get(DatabasePresenceVerifier::class));

        return $validatorFactory;
    }
}
