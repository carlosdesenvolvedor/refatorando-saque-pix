<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Account;
use App\Model\AccountWithdraw;
use App\Model\AccountWithdrawPix;
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

    public function createWithdrawal(string $accountId, array $data): AccountWithdraw
    {
        // Normalize schedule
        $scheduledFor = null;
        if (! empty($data['schedule'])) {
            try {
                $scheduledFor = Carbon::parse($data['schedule']);
            } catch (\Throwable $e) {
                $scheduledFor = null;
            }
        }

        $isScheduled = $scheduledFor !== null;
        $amount = (float) $data['amount'];

        /** @var AccountWithdraw $withdrawal */
        $withdrawal = Db::transaction(function () use ($accountId, $data, $amount, $scheduledFor, $isScheduled) {
            $account = Account::query()->where('id', $accountId)->lockForUpdate()->first();

            if (! $account) {
                throw new \App\Exception\BusinessException('Account not found');
            }

            // Deduct balance only if not scheduled (immediate withdrawal)
            if (! $isScheduled) {
                $currentBalance = (float) $account->balance;
                if ($currentBalance < $amount) {
                    throw new \App\Exception\BusinessException('Saldo insuficiente');
                }
                $account->balance = number_format($currentBalance - $amount, 2, '.', '');
                $account->save();
            }

            // 1. Instanciar e Salvar AccountWithdraw
            $withdrawal = new AccountWithdraw();
            $withdrawal->account_id = $accountId;
            $withdrawal->method = $data['method'];
            $withdrawal->amount = $amount;
            $withdrawal->scheduled = $isScheduled;
            $withdrawal->scheduled_for = $scheduledFor ? $scheduledFor->toDateTimeString() : null;
            $withdrawal->done = ! $isScheduled;
            $withdrawal->error = false;
            $withdrawal->save(); // UUID gerado aqui pelo Model

            // 2. Instanciar e Salvar AccountWithdrawPix
            if (isset($data['pix'])) {
                $pix = new AccountWithdrawPix();
                $pix->account_withdraw_id = $withdrawal->id;
                $pix->type = $data['pix']['type'];
                $pix->key = $data['pix']['key'];
                $pix->save();
            }

            return $withdrawal;
        });

        // Dispatch job if immediate (AFTER transaction commit)
        if (! $isScheduled) {
            $this->asyncQueue->push(new SendWithdrawalEmailJob($withdrawal->id));
        }

        return $withdrawal;
    }

    public function processScheduledWithdrawals(): void
    {
        $withdrawals = AccountWithdraw::query()
            ->where('scheduled', true)
            ->where('done', false)
            ->where('error', false)
            ->where('scheduled_for', '<=', Carbon::now())
            ->get();

        foreach ($withdrawals as $withdrawal) {
            try {
                Db::transaction(function () use ($withdrawal) {
                    // Reload withdrawal and lock account
                    $withdrawal->refresh();
                    if ($withdrawal->done || $withdrawal->error) {
                        return;
                    }

                    $account = Account::query()->where('id', $withdrawal->account_id)->lockForUpdate()->first();

                    if (!$account) {
                        $withdrawal->update([
                            'error' => true,
                            'error_reason' => 'Conta não encontrada',
                            'done' => true,
                        ]);
                        return;
                    }

                    $amount = (float) $withdrawal->amount;
                    $currentBalance = (float) $account->balance;

                    if ($currentBalance < $amount) {
                        // Regra de Falha: Saldo insuficiente
                        $withdrawal->update([
                            'error' => true,
                            'error_reason' => 'Saldo insuficiente no momento da execução',
                        ]);
                        $this->logger->warning("Saque agendado {$withdrawal->id} falhou: Saldo insuficiente.");
                        return;
                    }

                    // Sucesso: Deduzir saldo e marcar como feito
                    $account->balance = number_format($currentBalance - $amount, 2, '.', '');
                    $account->save();

                    $withdrawal->update([
                        'done' => true,
                        'error' => false,
                    ]);

                    // Disparar Job de Email
                    $this->asyncQueue->push(new SendWithdrawalEmailJob($withdrawal->id));
                    
                    $this->logger->info("Saque agendado {$withdrawal->id} processado com sucesso.");
                });
            } catch (\Throwable $e) {
                $this->logger->error("Erro ao processar saque agendado {$withdrawal->id}: " . $e->getMessage());
            }
        }
    }
}