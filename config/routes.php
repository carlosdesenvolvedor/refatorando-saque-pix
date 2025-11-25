<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\HttpServer\Router\Router;

Router::addServer('http', function () {
    // Rotas para Contas (Accounts)
    Router::post('/accounts', 'App\Controller\AccountController@store');
    Router::post('/accounts/{id}/deposit', 'App\Controller\AccountController@deposit');

    // Rotas para Chaves PIX (PixKeys)
    Router::post('/pix-keys', 'App\Controller\PixKeyController@store');

    // Rotas para Saques (Withdrawals)
    // Router::get('/withdrawals', 'App\Controller\WithdrawalController@index'); // TODO: Implementar método index
    Router::post('/withdrawals', 'App\Controller\WithdrawalController@store');
    Router::patch('/withdrawals/{id}/cancel', 'App\Controller\WithdrawalController@cancel');

    // Rota raiz para health check
    Router::get('/', function () {
        return ['status' => 'ok', 'message' => 'Saque PIX API is running.'];
    });

    Router::get('/favicon.ico', function () {
        return '';
    });

    Router::get('/debug-mail', function () {
        try {
            // 1. Pega o último saque com o relacionamento 'account' carregado (Eager Loading)
            $withdrawal = \App\Model\Withdrawal::with('account')->orderBy('id', 'desc')->first();

            if (!$withdrawal) {
                return 'Nenhum saque encontrado no banco. Faça um saque antes de testar.';
            }

            // 2. Instancia o Mailable
            $mail = new \App\Mail\ScheduledPixMail($withdrawal);

            // 3. Tenta renderizar o HTML (Se der erro aqui, veremos na tela)
            return $mail->render();
        } catch (\Throwable $e) {
            // 4. Mostra o erro completo
            return "<h1>ERRO CRÍTICO NA VIEW:</h1>" . "<h3>" . $e->getMessage() . "</h3>" . "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    });
});
