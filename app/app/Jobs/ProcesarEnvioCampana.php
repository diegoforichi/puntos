<?php

namespace App\Jobs;

use App\Mail\CampanaMail;
use App\Models\CampanaEnvio;
use App\Models\Tenant;
use App\Services\NotificationConfigResolver;
use App\Services\WhatsAppService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcesarEnvioCampana implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public int $envioId)
    {
        $this->queue = 'campanas';
    }

    public function handle(NotificationConfigResolver $configResolver, WhatsAppService $whatsAppService): void
    {
        // Buscar el envío en todos los tenants
        $tenants = Tenant::where('estado', 'activo')->get();

        $envio = null;

        foreach ($tenants as $tenant) {
            $sqlitePath = $tenant->getSqlitePath();
            if (! file_exists($sqlitePath)) {
                continue;
            }

            // Configurar conexión tenant
            Config::set('database.connections.tenant', [
                'driver' => 'sqlite',
                'database' => $sqlitePath,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]);
            DB::purge('tenant');

            // Buscar el envío
            $found = DB::connection('tenant')->table('campana_envios')->where('id', $this->envioId)->first();

            if ($found) {
                $envio = CampanaEnvio::with(['campana', 'cliente'])->find($this->envioId);
                break;
            }
        }

        if (! $envio) {
            return;
        }

        // Continuar con el procesamiento normal
        $envio = CampanaEnvio::with(['campana', 'cliente'])->find($this->envioId);

        if (! $envio || ! $envio->campana || ! $envio->cliente) {
            return;
        }

        $campana = $envio->campana;

        // Obtener el tenant desde MySQL usando el tenant_id de la campaña
        $tenantModel = null;
        if ($campana->tenant_id) {
            $tenantModel = \App\Models\Tenant::on('mysql')->find($campana->tenant_id);
        }

        $cliente = $envio->cliente;

        try {
            if ($envio->canal === 'whatsapp') {
                $config = $configResolver->resolveWhatsAppConfig($tenantModel);

                if (! ($config['usar_canal'] ?? false)) {
                    $envio->registrarFallo('WhatsApp deshabilitado.');
                    $campana->incrementarTotales('whatsapp', false);

                    return;
                }

                $mensaje = $campana->construirMensajeWhatsapp($cliente, $tenantModel);

                if (! $mensaje) {
                    $envio->registrarFallo('Mensaje de WhatsApp vacío o canal deshabilitado.');
                    $campana->incrementarTotales('whatsapp', false);

                    return;
                }

                $telefonoDestino = $cliente->telefono_whatsapp;

                if (! $telefonoDestino) {
                    $envio->registrarFallo('Cliente sin teléfono válido para WhatsApp.');
                    $campana->incrementarTotales('whatsapp', false);

                    return;
                }

                $respuesta = $whatsAppService->enviar($config, $telefonoDestino, $mensaje, $tenantModel);

                if ($respuesta['success'] ?? false) {
                    $envio->marcarEnviado();
                    $campana->incrementarTotales('whatsapp', true);
                } else {
                    $envio->registrarFallo($respuesta['message'] ?? 'Error desconocido');
                    $campana->incrementarTotales('whatsapp', false);
                }

                sleep(2);
            }

            if ($envio->canal === 'email') {
                $config = $configResolver->resolveEmailConfig($tenantModel);

                if (! ($config['usar_canal'] ?? false)) {
                    $envio->registrarFallo('Email deshabilitado.');
                    $campana->incrementarTotales('email', false);

                    return;
                }

                $contenido = $campana->obtenerContenidoEmail();

                if (empty($contenido['cuerpo'])) {
                    $envio->registrarFallo('Contenido de email vacío.');
                    $campana->incrementarTotales('email', false);

                    return;
                }

                if (empty($cliente->email)) {
                    $envio->registrarFallo('Cliente sin email configurado.');
                    $campana->incrementarTotales('email', false);

                    return;
                }

                $usaEmailPersonalizado = (($config['source'] ?? 'global') === 'tenant') && ($config['activo'] ?? false);
                $cacheKey = null;
                $enviosRealizados = 0;

                if ($usaEmailPersonalizado) {
                    $cacheKey = sprintf('tenant:%s:email_quota:%s', $tenantModel?->id ?? 'global', now()->format('Y-m-d'));
                    $ttl = now()->diffInSeconds(now()->endOfDay()) ?: 60;
                    $enviosRealizados = Cache::remember($cacheKey, $ttl, fn () => 0);

                    if ($enviosRealizados >= 50) {
                        Log::info('Límite diario de emails alcanzado. Se reintentará mañana.', [
                            'tenant' => $tenantModel?->rut,
                            'campana_id' => $campana->id,
                            'envio_id' => $envio->id,
                        ]);

                        $reintento = now()->addDay()->startOfDay()->addMinutes(5);
                        self::dispatch($envio->id)->delay($reintento);

                        return;
                    }
                }

                config([
                    'mail.mailers.smtp.host' => $config['host'] ?? null,
                    'mail.mailers.smtp.port' => $config['port'] ?? 587,
                    'mail.mailers.smtp.username' => $config['username'] ?? null,
                    'mail.mailers.smtp.password' => $config['password'] ?? null,
                    'mail.mailers.smtp.encryption' => $config['encryption'] ?? null,
                    'mail.from.address' => $config['from_address'] ?? config('mail.from.address'),
                    'mail.from.name' => $config['from_name'] ?? config('mail.from.name'),
                ]);

                Mail::mailer('smtp')->to($cliente->email)->send(new CampanaMail($campana, $cliente, $contenido));
                $envio->marcarEnviado();
                $campana->incrementarTotales('email', true);

                if ($usaEmailPersonalizado && $cacheKey) {
                    $ttl = now()->diffInSeconds(now()->endOfDay()) ?: 60;
                    Cache::put($cacheKey, $enviosRealizados + 1, $ttl);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error enviando campaña', [
                'envio_id' => $envio->id,
                'campana_id' => $campana->id,
                'error' => $e->getMessage(),
            ]);

            $envio->registrarFallo($e->getMessage());
            $campana->incrementarTotales($envio->canal, false);

            if ($envio->intentos < 3) {
                self::dispatch($envio->id)->delay(now()->addMinutes(5));
            }
        } finally {
            $campana->marcarCompletadaSiCorresponde();
        }
    }
}
