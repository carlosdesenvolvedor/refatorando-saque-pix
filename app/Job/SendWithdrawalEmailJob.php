<?php

declare(strict_types=1);

namespace App\Job;

use App\Mail\ScheduledPixMail;
use App\Model\AccountWithdraw;
use FriendsOfHyperf\Mail\Facade\Mail;
use Hyperf\AsyncQueue\Job;
use Hyperf\Context\ApplicationContext;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;
use Hyperf\Contract\StdoutLoggerInterface; // Fallback log

class SendWithdrawalEmailJob extends Job
{
    public string $withdrawalId;

    public function __construct(string $withdrawalId)
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

        // Carrega o relacionamento 'pix' para obter a chave (e-mail)
        $withdrawal = AccountWithdraw::with('pix')->find($this->withdrawalId);

        if (!$withdrawal) {
            $logger->warning("JOB ABORTADO: Saque {$this->withdrawalId} não encontrado no banco.");
            return;
        }

        // Logs de depuração
        $logger->info("DEBUG JOB - ID Saque: " . $withdrawal->id);
        $logger->info("DEBUG JOB - Tem Pix?: " . ($withdrawal->pix ? 'SIM' : 'NAO'));
        
        if ($withdrawal->pix) {
            $logger->info("DEBUG JOB - Chave Pix: " . $withdrawal->pix->key);
        }

        // O destinatário é a chave PIX (assumindo que o tipo é email, conforme regra do PDF)
        $recipientEmail = $withdrawal->pix->key ?? null;

        // Verificação de segurança: não prosseguir se o e-mail não estiver definido.
        if (empty($recipientEmail)) {
            $logger->warning("JOB ABORTADO: O saque ID {$this->withdrawalId} não possui uma chave PIX (e-mail) associada.");
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