<?php

declare(strict_types=1);

namespace App\Mail;

use App\Model\Withdrawal;
use FriendsOfHyperf\Mail\Mailable;

class ScheduledPixMail extends Mailable
{
    /**
     * @var Withdrawal O modelo de saque com todos os dados.
     */
    public Withdrawal $withdrawal;

    public function __construct(Withdrawal $withdrawal)
    {
        $this->withdrawal = $withdrawal;
    }

    /**
     * Constrói a mensagem de e-mail.
     */
    public function build(): self
    {
        return $this
            ->subject('Seu saque PIX foi processado')
            ->view('withdrawal'); // Aponta para storage/view/withdrawal.blade.php
    }
}