<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\WithdrawalService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class WithdrawalController extends AbstractController
{
    #[Inject]
    private WithdrawalService $withdrawalService;

    #[Inject]
    private ValidatorFactoryInterface $validatorFactory;

    public function store(RequestInterface $request, ResponseInterface $response, string $accountId): PsrResponseInterface
    {
        $validator = $this->validatorFactory->make(
            $request->all(),
            [
                'amount' => 'required|numeric|min:0.01',
                'method' => 'required|string|in:PIX',
                'pix.type' => 'required|string',
                'pix.key' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return $response->json(['errors' => $validator->errors()->all()])->withStatus(422);
        }

        $data = $validator->validated();

        try {
            $withdrawal = $this->withdrawalService->processWithdrawal($accountId, $data);
            return $response->json($withdrawal)->withStatus(201);
        } catch (\Throwable $e) {
            return $response->json(['error' => $e->getMessage()])->withStatus(400);
        }
    }
}