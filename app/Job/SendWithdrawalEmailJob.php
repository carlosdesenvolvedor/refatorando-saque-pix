<?php

declare(strict_types=1);

namespace App\Job;

use App\Mail\ScheduledPixMail;
use App\Model\Withdrawal;
use FriendsOfHyperf\Mail\Facade\Mail;
use Hyperf\AsyncQueue\Job;
use Hyperf\Context\ApplicationContext;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;
use Hyperf\Contract\StdoutLoggerInterface; // Fallback log

class SendWithdrawalEmailJob extends Job
{
    public int $withdrawalId;

    public function __construct(int $withdrawalId)
    {
        $this->withdrawalId = $withdrawalId;
    }

    public function handle()
    {
        // Tenta pegar o Logger, se falhar usa o Stdout
        $container = ApplicationContext::getContainer();
        $logger = $container->has(LoggerInterface::class) 
            ? $container->get(LoggerFactory::class)->get('mail') 
            : $container->get(StdoutLoggerInterface::class);

        $logger->info("JOB INICIADO: Processando saque ID {$this->withdrawalId}...");

        // CORREÇÃO CRÍTICA: Sempre buscar o modelo do zero dentro do Job com Eager Loading.
        $withdrawal = Withdrawal::with('account')->find($this->withdrawalId);

        if (!$withdrawal) {
            $logger->warning("JOB ABORTADO: Saque {$this->withdrawalId} não encontrado no banco.");
            return;
        }

        // Logs de depuração agressivos para inspecionar os dados
        $logger->info("DEBUG JOB - ID Saque: " . $withdrawal->id);
        $logger->info("DEBUG JOB - Tem Conta?: " . ($withdrawal->account ? 'SIM' : 'NAO'));
        if ($withdrawal->account) {
            $logger->info("DEBUG JOB - Email na Conta: " . ($withdrawal->account->email ?: 'VAZIO'));
        }
        // Fim dos logs de depuração

        // Busca o e-mail dinamicamente da conta associada ao saque.
        $recipientEmail = $withdrawal->account->email ?? null;

        // Verificação de segurança mais robusta: a conta foi carregada?
        if (!$withdrawal->account) {
            $logger->error("JOB FALHOU: Não foi possível carregar a conta associada ao saque ID {$this->withdrawalId}.");
            return;
        }

        // Verificação de segurança: não prosseguir se o e-mail não estiver definido.
        if (empty($recipientEmail)) {
            $logger->warning("JOB ABORTADO: A conta associada ao saque ID {$this->withdrawalId} não possui um e-mail cadastrado.");
            return;
        }

        try {
            // Envio do E-mail
            Mail::to($recipientEmail)->send(new ScheduledPixMail($withdrawal));

            $logger->info("JOB SUCESSO: E-mail enviado para {$recipientEmail}!");
        } catch (\Throwable $e) {
            $logger->error("JOB ERRO CRÍTICO: " . $e->getMessage());
            $logger->error($e->getTraceAsString());
        }
    }
}