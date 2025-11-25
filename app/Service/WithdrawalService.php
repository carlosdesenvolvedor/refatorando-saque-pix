<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Account;
use App\Model\Withdrawal;
use App\Job\SendWithdrawalEmailJob;
use Carbon\Carbon;
use Psr\Log\LoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;

class WithdrawalService
{
    private DriverInterface $asyncQueue;

    public function __construct(private LoggerInterface $logger, DriverFactory $driverFactory)
    {
        $this->asyncQueue = $driverFactory->get('default');
    }

    public function createWithdrawal(array $data): Withdrawal
    {
        // Normalize scheduled_for
        $scheduledFor = null;
        if (! empty($data['scheduled_for'])) {
            try {
                $scheduledFor = Carbon::parse($data['scheduled_for']);
            } catch (\Throwable $e) {
                $scheduledFor = null;
            }
        }

        $status = $scheduledFor ? 'scheduled' : 'completed';

        $accountId = isset($data['account_id']) ? (int) $data['account_id'] : 0;
        $pixKeyId = isset($data['pix_key_id']) ? (int) $data['pix_key_id'] : 0;
        $amount = isset($data['amount']) ? (float) $data['amount'] : 0.0;

        if ($accountId <= 0) {
            throw new \InvalidArgumentException('account_id is required');
        }
        if ($pixKeyId <= 0) {
            throw new \InvalidArgumentException('pix_key_id is required');
        }
        if ($amount <= 0) {
            throw new \InvalidArgumentException('amount must be greater than zero');
        }

        return Db::transaction(function () use ($accountId, $pixKeyId, $amount, $scheduledFor, $status) {
            $account = Account::query()->where('id', $accountId)->lockForUpdate()->first();

            if (! $account) {
                throw new \RuntimeException('Account not found');
            }

            if ($status === 'completed') {
                $currentBalance = (float) $account->balance;
                if ($currentBalance < $amount) {
                    throw new \RuntimeException('Insufficient funds');
                }
                $account->balance = number_format($currentBalance - $amount, 2, '.', '');
                $account->save();
            }

            $withdrawal = Withdrawal::create([
                'account_id' => $accountId,
                'amount' => number_format($amount, 2, '.', ''),
                'pix_key_id' => $pixKeyId,
                'status' => $status,
                'scheduled_for' => $scheduledFor ? $scheduledFor->toDateTimeString() : null,
            ]);

            // Despacha o job para a fila assíncrona se o saque for imediato
            if ($status === 'completed') {
                $this->asyncQueue->push(new SendWithdrawalEmailJob($withdrawal->id));
            }
            // ADICIONAR ESTA LINHA:
            $this->asyncQueue->push(new SendWithdrawalEmailJob($withdrawal->id));

            return $withdrawal;
        });
    }
}