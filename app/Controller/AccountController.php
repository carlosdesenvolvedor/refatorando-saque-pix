<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Account;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class AccountController extends AbstractController
{
    public function __construct(private ValidatorFactoryInterface $validatorFactory)
    {
    }

    /**
     * Cria uma nova conta.
     */
    public function store(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        // 1. Valida os dados recebidos (apenas o nome por enquanto)
        $validator = $this->validatorFactory->make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
            ]
        );

        if ($validator->fails()) {
            return $response->json(['errors' => $validator->errors()])->withStatus(422);
        }

        // 2. Cria a conta no banco de dados
        $account = Account::create($validator->validated());
        

        // 3. Retorna a conta criada com o status 201 (Created)
        return $response->json($account)->withStatus(201);
    }

    /**
     * Consulta o saldo de uma conta específica.
     */
    public function balance(string $id, ResponseInterface $response): \Psr\Http\Message\ResponseInterface
    {
        // 1. Usa o Account Model para encontrar a conta pelo ID
        $account = Account::find($id);

        // 2. Verifica se a conta existe
        if (!$account) {
            return $response->json(['message' => 'Account not found'])->withStatus(404);
        }

        // 3. Retorna o saldo da conta em um JSON
        return $response->json(['balance' => $account->balance]);
    }
     /**
     * Deposita um valor em uma conta específica.
     */
    public function deposit(
        string $id,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        // 1. Valida o valor recebido
        $validator = $this->validatorFactory->make(
            $request->all(),
            ['amount' => 'required|numeric|gt:0'] // gt:0 = maior que zero
        );

        if ($validator->fails()) {
            return $response->json(['errors' => $validator->errors()])->withStatus(422);
        }

        // 2. Encontra a conta
        $account = Account::find($id);
        if (!$account) {
            return $response->json(['message' => 'Account not found'])->withStatus(404);
        }

        // 3. Adiciona o saldo e salva
        $data = $validator->validated();
        $account->balance += (float) $data['amount'];
        $account->save();

        // 4. Retorna a conta atualizada
        return $response->json($account);
    }
}
