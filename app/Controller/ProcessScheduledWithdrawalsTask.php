<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\WithdrawalService;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;
use Throwable;

#[Crontab(name: "ProcessScheduledWithdrawals", rule: "* * * * *", callback: "execute", memo: "Processa saques agendados a cada minuto.")]
class ProcessScheduledWithdrawalsTask
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerFactory $loggerFactory,
        private readonly WithdrawalService $service
    ) {
        $this->logger = $loggerFactory->get('crontab');
    }

    public function execute(): void
    {
        $this->logger->info('CRONJOB START: Verificando saques agendados...');
        try {
            $this->service->processScheduledWithdrawals();
            $this->logger->info('CRONJOB END: Verificação de saques finalizada com sucesso.');
        } catch (Throwable $e) {
            $this->logger->error('CRONJOB FAILED: Erro ao processar saques agendados.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }
}
