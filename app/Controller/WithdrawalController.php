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

    public function store(RequestInterface $request, ResponseInterface $response): \Psr\Http\Message\ResponseInterface
    {
        $data = $request->all();

        $validator = $this->validatorFactory->make($data, [
            'account_id' => 'required|integer|exists:accounts,id',
            'pix_key_id' => 'required|integer|exists:pix_keys,id',
            'amount' => 'required|numeric|min:0.01',
            'scheduled_for' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $response->json(['errors' => $validator->errors()])->withStatus(422);
        }

        try {
            $withdrawal = $this->withdrawalService->createWithdrawal($validator->validated());
            return $response->json($withdrawal)->withStatus(201);
        } catch (Throwable $e) {
            $this->logger->error('WithdrawalController::store error: ' . $e->getMessage(), ['exception' => $e]);
            return $response->json(['message' => 'An unexpected error occurred: ' . $e->getMessage()])->withStatus(500);
        }
    }
}