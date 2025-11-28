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
    Router::post('/account/{id}/deposit', 'App\Controller\AccountController@deposit');
    Router::get('/account/{id}/balance', 'App\Controller\AccountController@balance');

    // Rotas para Chaves PIX (PixKeys)
    Router::post('/pix-keys', 'App\Controller\PixKeyController@store');

    // Rotas para Saques (Withdrawals)
    // Rota antiga de saque, desativada para dar lugar à nova especificação.
    // Router::post('/withdrawals', 'App\Controller\WithdrawalController@store');
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

    Router::get('/debug-server', function () {
        $config = \Hyperf\Context\ApplicationContext::getContainer()->get(\Hyperf\Contract\ConfigInterface::class);
        
        return [
            'php_version' => phpversion(),
            'extensions' => get_loaded_extensions(),
            'drivers_pdo' => \PDO::getAvailableDrivers(), // <--- ISSO É O MAIS IMPORTANTE
            'db_config' => $config->get('databases.default'), // Mostra o que o Hyperf leu do .env
            'env_vars' => [
                'DB_DRIVER' => getenv('DB_DRIVER'),
                'DB_CONNECTION' => getenv('DB_CONNECTION'),
            ]
        ];
    });

    // Nova rota de saque conforme especificação
    Router::post('/account/{accountId}/balance/withdraw', 'App\Controller\WithdrawalController@store');
});
