<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Exception\BusinessException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class BusinessExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // Debug logging
        $logger = \Hyperf\Context\ApplicationContext::getContainer()->get(\Hyperf\Contract\StdoutLoggerInterface::class);
        $logger->info('BusinessExceptionHandler caught exception: ' . $throwable->getMessage());

        $this->stopPropagation();
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(422)
            ->withBody(new SwooleStream(json_encode([
                'message' => $throwable->getMessage(),
                'error' => 'business_logic_error'
            ])));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof BusinessException;
    }
}
