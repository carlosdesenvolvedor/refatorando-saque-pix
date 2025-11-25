<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HttpNotFoundExceptionHandler extends ExceptionHandler
{
    protected StdoutLoggerInterface $logger;
    protected RequestInterface $request;

    public function __construct(StdoutLoggerInterface $logger, RequestInterface $request)
    {
        $this->logger = $logger;
        $this->request = $request;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // Intercepta apenas a exceção NotFoundHttpException
        if ($throwable instanceof NotFoundHttpException) {
            $this->stopPropagation(); // Impede que outros handlers processem a exceção

            // Loga a informação útil
            $method = $this->request->getMethod();
            $uri = $this->request->getUri()->getPath();
            $this->logger->warning(sprintf('404 Not Found: Method [%s] for path [%s]', $method, $uri));

            // Retorna a resposta padrão de "Not Found" do Hyperf
            return $response->withStatus(404)->withBody(new \Hyperf\Engine\Http\Stream('Not Found.'));
        }

        // Se não for a exceção que queremos, passa para o próximo handler
        return $response;
    }

    public function isValid(Throwable $throwable): bool
    {
        return true; // Queremos que este handler seja sempre verificado
    }
}
