<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResumenDiario extends Mailable
{
    use Queueable, SerializesModels;

    public Tenant $tenant;
    public array $stats;

    public function __construct(Tenant $tenant, array $stats)
    {
        $this->tenant = $tenant;
        $this->stats = $stats;
    }

    public function build(): self
    {
        $fecha = now()->subDay()->format('d/m/Y');

        return $this
            ->subject("Sistema de Puntos - Resumen Diario [{$this->tenant->nombre_comercial}] - {$fecha}")
            ->view('emails.resumen-diario');
    }
}

