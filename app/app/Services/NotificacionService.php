<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class NotificacionService
{
    private Tenant $tenant;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Notificar al cliente sobre puntos canjeados
     */
    public function notificarCanje(array $cliente, float $puntos, float $saldoRestante): void
    {
        $eventos = Configuracion::get('eventos_whatsapp', []);

        if (! ($eventos['puntos_canjeados'] ?? false)) {
            return;
        }

        $telefono = $cliente['telefono'] ?? null;

        if (! $telefono) {
            return;
        }

        $contacto = Configuracion::getContacto();
        $nombreComercio = $contacto['nombre_comercial'] ?? $this->tenant->nombre_comercial;

        $mensaje = sprintf(
            'Hola %s, canjeaste %.2f puntos en %s. Tu saldo actual es %.2f puntos. ¡Gracias!',
            $cliente['nombre'],
            $puntos,
            $nombreComercio,
            $saldoRestante
        );

        $this->enviarWhatsapp($telefono, $mensaje);
    }

    /**
     * Notificar al cliente sobre puntos por vencer
     */
    public function notificarPuntosProximosAVencer(array $cliente, float $puntos, string $fechaVencimiento): void
    {
        $eventos = Configuracion::get('eventos_whatsapp', []);

        if (! ($eventos['puntos_por_vencer'] ?? false)) {
            return;
        }

        $telefono = $cliente['telefono'] ?? null;

        if (! $telefono) {
            return;
        }

        $contacto = Configuracion::getContacto();
        $nombreComercio = $contacto['nombre_comercial'] ?? $this->tenant->nombre_comercial;

        $mensaje = sprintf(
            'Hola %s, tienes %.2f puntos que vencen el %s en %s. ¡Úsalos antes de perderlos!',
            $cliente['nombre'],
            $puntos,
            $fechaVencimiento,
            $nombreComercio
        );

        $this->enviarWhatsapp($telefono, $mensaje);
    }

    /**
     * Dar la bienvenida a un nuevo cliente
     */
    public function notificarBienvenida(array $cliente): void
    {
        $eventos = Configuracion::get('eventos_whatsapp', []);

        if (! ($eventos['bienvenida_nuevos'] ?? false)) {
            return;
        }

        $telefono = $cliente['telefono'] ?? null;

        if (! $telefono) {
            return;
        }

        $contacto = Configuracion::getContacto();
        $nombreComercio = $contacto['nombre_comercial'] ?? $this->tenant->nombre_comercial;

        $mensaje = sprintf(
            '¡Bienvenido %s! Ya eres parte del programa de puntos de %s. Acumula puntos con cada compra.',
            $cliente['nombre'],
            $nombreComercio
        );

        $this->enviarWhatsapp($telefono, $mensaje);
    }

    /**
     * Notificar sobre una promoción activa
     */
    public function notificarPromocion(array $cliente, string $descripcionPromocion, string $fechaFin): void
    {
        $eventos = Configuracion::get('eventos_whatsapp', []);

        if (! ($eventos['promociones_activas'] ?? false)) {
            return;
        }

        $telefono = $cliente['telefono'] ?? null;

        if (! $telefono) {
            return;
        }

        $contacto = Configuracion::getContacto();
        $nombreComercio = $contacto['nombre_comercial'] ?? $this->tenant->nombre_comercial;

        $mensaje = sprintf(
            '¡Oferta especial en %s! %s. Válida hasta %s.',
            $nombreComercio,
            $descripcionPromocion,
            $fechaFin
        );

        $this->enviarWhatsapp($telefono, $mensaje);
    }

    private function enviarWhatsapp(string $telefono, string $mensaje): void
    {
        $resolver = app(NotificationConfigResolver::class);
        $config = $resolver->resolveWhatsAppConfig($this->tenant);

        $resultado = WhatsAppService::enviar($config, $telefono, $mensaje, $this->tenant);

        if (! ($resultado['success'] ?? false)) {
            Log::warning('WhatsApp no enviado', [
                'tenant' => $this->tenant->rut,
                'telefono' => $telefono,
                'motivo' => $resultado['message'] ?? 'motivo desconocido',
            ]);
        }
    }
}
