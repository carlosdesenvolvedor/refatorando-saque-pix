<?php

declare(strict_types=1);

namespace App\Mail;

use App\Model\AccountWithdraw;
use FriendsOfHyperf\Mail\Mailable;

class ScheduledPixMail extends Mailable
{
    /**
     * @var AccountWithdraw O modelo de saque com todos os dados.
     */
    public AccountWithdraw $withdrawal;

    public function __construct(AccountWithdraw $withdrawal)
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