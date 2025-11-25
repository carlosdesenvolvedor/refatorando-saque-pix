<?php

declare(strict_types=1);

namespace App\Job;

use App\Mail\ScheduledPixMail;
use FriendsOfHyperf\Mail\Mail;
use Hyperf\AsyncQueue\Job;
use Hyperf\Di\Annotation\Inject;

class SendScheduledPixEmailJob extends Job
{
    #[Inject]
    private Mail $mailer;

    /**
     * @var array Os dados para o e-mail.
     */
    public array $data;

    /**
     * O número de tentativas de execução do job.
     */
    protected int $maxAttempts = 3;

    public function __construct(array $data)
    {
        // Aqui recebemos os dados do controller, como e-mail do destinatário e detalhes do PIX.
        $this->data = $data;
    }

    public function handle()
    {
        // Extrai o e-mail do destinatário dos dados
        $recipientEmail = $this->data['email'];

        // Cria a instância do Mailable com os dados
        $mailable = new ScheduledPixMail($this->data);

        // Envia o e-mail para o destinatário usando o mailer injetado
        $this->mailer->to($recipientEmail)->send($mailable);
    }
}