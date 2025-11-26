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
        file_put_contents('/tmp/job_debug.txt', "JOB START: {$this->withdrawalId}\n", FILE_APPEND);

        // Carrega o relacionamento 'pix' e 'account' para obter dados de contato
        $withdrawal = AccountWithdraw::with(['pix', 'account'])->find($this->withdrawalId);

        if (!$withdrawal) {
            $logger->warning("JOB ABORTADO: Saque {$this->withdrawalId} não encontrado no banco.");
            return;
        }

        // Logs de depuração
        $logger->info("DEBUG JOB - ID Saque: " . $withdrawal->id);
        
        $pixKey = $withdrawal->pix->key ?? null;
        $accountEmail = $withdrawal->account->email ?? null;
        
        $recipientEmail = null;

        // 1. Tenta usar a chave PIX se for um e-mail válido
        if ($pixKey && filter_var($pixKey, FILTER_VALIDATE_EMAIL)) {
            $recipientEmail = $pixKey;
            $logger->info("Destinatário definido pela Chave PIX: {$recipientEmail}");
        } 
        // 2. Se não, tenta usar o e-mail da conta
        elseif ($accountEmail && filter_var($accountEmail, FILTER_VALIDATE_EMAIL)) {
            $recipientEmail = $accountEmail;
            $logger->info("Chave PIX ('{$pixKey}') não é e-mail. Destinatário definido pela Conta: {$recipientEmail}");
        }
        
        file_put_contents('/tmp/job_debug.txt', "Recipient: " . ($recipientEmail ?? 'NULL') . " | PixKey: " . ($pixKey ?? 'NULL') . "\n", FILE_APPEND);

        // Verificação de segurança: não prosseguir se o e-mail não estiver definido.
        if (empty($recipientEmail)) {
            $logger->warning("JOB ABORTADO: Não foi possível determinar um e-mail válido para o saque ID {$this->withdrawalId}. Key: {$pixKey}, Account Email: {$accountEmail}");
            return;
        }

        try {
            // Envio do E-mail
            Mail::to($recipientEmail)->send(new ScheduledPixMail($withdrawal));

            $logger->info("JOB SUCESSO: E-mail enviado para {$recipientEmail}!");
            file_put_contents('/tmp/job_debug.txt', "JOB SUCCESS: Sent to {$recipientEmail}\n", FILE_APPEND);
        } catch (\Throwable $e) {
            $logger->error("JOB ERRO CRÍTICO: " . $e->getMessage());
            $logger->error($e->getTraceAsString());
            file_put_contents('/tmp/job_debug.txt', "JOB ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
}