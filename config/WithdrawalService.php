<?php

declare(strict_types=1);

namespace App\Service;

use App\Job\SendWithdrawalEmailJob;
use App\Model\Account;
use App\Model\AccountWithdraw;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\DbConnection\Db;
use Hyperf\Stringable\Str;

class WithdrawalService
{
    private $queue;

    public function __construct(DriverFactory $driverFactory)
    {
        $this->queue = $driverFactory->get('default');
    }

    public function processWithdrawal(string $accountId, array $data): AccountWithdraw
    {
        $account = Account::findOrFail($accountId);
        $amount = (float) $data['amount'];

        if ($account->balance < $amount) {
            throw new \Exception('Insufficient balance.');
        }

        return Db::transaction(function () use ($account, $amount, $data) {
            // 1. Debita o valor da conta
            $account->balance -= $amount;
            $account->save();

            // 2. Cria o registro de saque (account_withdraw)
            $withdrawal = AccountWithdraw::create([
                'id' => (string) Str::uuid(),
                'account_id' => $account->id,
                'method' => $data['method'],
                'amount' => $amount,
                'scheduled' => false, // Lógica de agendamento pode ser adicionada aqui
                'done' => true,
                'error' => false,
            ]);

            // 3. Cria o registro de dados do PIX (account_withdraw_pix)
            $withdrawal->pix()->create([
                'type' => $data['pix']['type'],
                'key' => $data['pix']['key'],
            ]);

            // 4. Dispara o job para enviar o e-mail de confirmação
            $this->queue->push(new SendWithdrawalEmailJob($withdrawal));

            // Recarrega o relacionamento para retornar o objeto completo
            $withdrawal->load('pix');

            return $withdrawal;
        });
    }
}