<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\PixKeyServiceInterface;
use App\Exception\BusinessException;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Throwable;

class PixKeyController extends AbstractController
{
    public function __construct(
        private readonly ValidatorFactoryInterface $validatorFactory,
        private readonly PixKeyServiceInterface $pixKeyService,
    ) {
    }

    public function store(RequestInterface $request, ResponseInterface $response)
    {
        $validator = $this->validatorFactory->make(
            $request->all(),
            [
                'account_id' => 'required|integer|exists:accounts,id',
                'kind' => 'required|in:email,cpf,cnpj,phone,random',
                'key' => 'required|string|max:255',
            ]
        );

        if ($validator->fails()) {
            return $response->json(['errors' => $validator->errors()])->withStatus(422);
        }

        try {
            $this->pixKeyService->create($validator->validated());

            return $response->withStatus(201);
        } catch (BusinessException $e) {
            return $response->json(['message' => $e->getMessage()])->withStatus($e->getCode());
        } catch (Throwable $e) {
            // Log the error for debugging
            // logger()->error('Error creating Pix key', ['exception' => $e]);
            return $response->json(['message' => 'An unexpected error occurred.'])->withStatus(500);
        }
    }
}