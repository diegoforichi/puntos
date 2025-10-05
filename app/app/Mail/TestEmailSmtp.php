<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestEmailSmtp extends Mailable
{
    use Queueable, SerializesModels;

    public function build(): self
    {
        return $this->subject('Prueba SMTP - Sistema de Puntos')
            ->view('emails.test-email');
    }
}
