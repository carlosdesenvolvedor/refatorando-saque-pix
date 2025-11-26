<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\WithdrawalService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @Controller(prefix="withdrawals")
 */
class WithdrawalController extends AbstractController
{
    public function __construct(
        private readonly ValidatorFactoryInterface $validatorFactory,
        private readonly WithdrawalService $withdrawalService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function store(string $accountId, RequestInterface $request, ResponseInterface $response): \Psr\Http\Message\ResponseInterface
    {
        $data = $request->all();

        $validator = $this->validatorFactory->make($data, [
            'method' => 'required|in:PIX',
            'amount' => 'required|numeric|gt:0',
            'pix.type' => 'required|in:email,cpf',
            'pix.key' => 'required',
            'schedule' => 'nullable|date|after:now|before:+7 days',
        ]);

        if ($validator->fails()) {
            return $response->json(['errors' => $validator->errors()])->withStatus(422);
        }

        $withdrawal = $this->withdrawalService->createWithdrawal($accountId, $validator->validated());
        return $response->json($withdrawal)->withStatus(201);
    }
}