<?php

declare(strict_types=1);

namespace App\Job;

use App\Mail\WithdrawalSuccessMail;
use App\Model\AccountWithdraw;
use Hyperf\AsyncQueue\Job;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\MailerInterface;

class SendWithdrawalEmailJob extends Job
{
    public AccountWithdraw $withdrawal;

    #[Inject]
    protected MailerInterface $mailer;

    public function __construct(AccountWithdraw $withdrawal)
    {
        // Carrega o relacionamento 'pix' se não estiver carregado
        $this->withdrawal = $withdrawal->relationLoaded('pix') ? $withdrawal : $withdrawal->load('pix');
    }

    public function handle()
    {
        // Injeção de dependência manual, pois o Job é desserializado
        if (!isset($this->mailer)) {
            $this->mailer = ApplicationContext::getContainer()->get(MailerInterface::class);
        }

        // O destinatário agora é a chave PIX
        $recipientEmail = $this->withdrawal->pix->key;

        $mail = new WithdrawalSuccessMail($this->withdrawal);
        $this->mailer->send($mail->to($recipientEmail));
    }
}