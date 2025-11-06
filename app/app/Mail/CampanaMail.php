<?php

namespace App\Mail;

use App\Models\Campana;
use App\Models\Cliente;
use App\Models\Configuracion;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampanaMail extends Mailable
{
    use SerializesModels;

    public function __construct(public Campana $campana, public Cliente $cliente, public array $contenido) {}

    public function build(): self
    {
        $contacto = Configuracion::getContacto();

        return $this->subject($this->contenido['asunto'] ?? $this->campana->titulo)
            ->view('emails.campana')
            ->with([
                'campana' => $this->campana,
                'cliente' => $this->cliente,
                'contenido' => $this->contenido,
                'contacto' => $contacto,
            ]);
    }
}
