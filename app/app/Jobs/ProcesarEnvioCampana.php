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

    public function __construct(public int $envioId, public int $tenantId)
    {
        $this->queue = 'campanas';
    }

    public function handle(NotificationConfigResolver $configResolver, WhatsAppService $whatsAppService): void
    {
        $tenantModel = Tenant::on('mysql')->find($this->tenantId);

        if (! $tenantModel) {
            return;
        }

        $sqlitePath = $tenantModel->getSqlitePath();

        if (! file_exists($sqlitePath)) {
            return;
        }

        Config::set('database.connections.tenant', [
            'driver' => 'sqlite',
            'database' => $sqlitePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::purge('tenant');
        DB::setDefaultConnection('tenant');

        $envio = CampanaEnvio::with(['campana', 'cliente'])->find($this->envioId);

        if (! $envio || ! $envio->campana || ! $envio->cliente) {
            return;
        }

        $campana = $envio->campana;

        if ($campana->tenant_id && (int) $campana->tenant_id !== $tenantModel->id) {
            return;
        }

        $originalUpdatedAt = $envio->updated_at;

        $query = DB::connection('tenant')->table('campana_envios')
            ->where('id', $envio->id)
            ->where('estado', 'pendiente');

        if ($originalUpdatedAt) {
            $query->where('updated_at', $originalUpdatedAt);
        } else {
            $query->whereNull('updated_at');
        }

        $tomado = $query->update([
            'updated_at' => now('America/Montevideo'),
        ]);

        if ($tomado === 0) {
            return;
        }

        $envio->refresh();

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

                // Delay aleatorio 4-7 segundos para evitar bloqueos de WhatsApp
                sleep(random_int(4, 7));
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

                $ahora = now();
                $origenConfig = $config['source'] ?? 'global';
                $usaEmailPersonalizado = ($origenConfig === 'tenant') && ($config['activo'] ?? false);
                $claveCuotaTenantDia = null;
                $claveCuotaGlobalHora = null;
                $claveCuotaGlobalDia = null;

                if ($usaEmailPersonalizado) {
                    $tenantId = $tenantModel?->id ?? 'global';
                    $claveCuotaTenantDia = sprintf('email_quota_tenant:%s:%s', $tenantId, $ahora->toDateString());

                    Cache::add($claveCuotaTenantDia, 0, $ahora->copy()->endOfDay());
                    $enviosTenantHoy = (int) Cache::get($claveCuotaTenantDia, 0);

                    if ($enviosTenantHoy >= 200) {
                        Log::info('Límite diario de emails (SMTP tenant) alcanzado. Se reintentará mañana.', [
                            'tenant' => $tenantModel?->rut,
                            'campana_id' => $campana->id,
                            'envio_id' => $envio->id,
                        ]);

                        $proximoIntento = $ahora->copy()->addDay()->startOfDay()->addMinutes(10);
                        $this->release($ahora->diffInSeconds($proximoIntento));

                        return;
                    }
                } else {
                    $claveCuotaGlobalHora = 'email_quota_global_hour:'.$ahora->format('Y-m-d_H');
                    $claveCuotaGlobalDia = 'email_quota_global_day:'.$ahora->toDateString();

                    Cache::add($claveCuotaGlobalHora, 0, $ahora->copy()->addHour());
                    Cache::add($claveCuotaGlobalDia, 0, $ahora->copy()->endOfDay());

                    $enviosGlobalHora = (int) Cache::get($claveCuotaGlobalHora, 0);
                    $enviosGlobalDia = (int) Cache::get($claveCuotaGlobalDia, 0);

                    if ($enviosGlobalHora >= 400) {
                        Log::info('Límite horario de emails (SMTP global) alcanzado. Reintentando en la próxima hora.', [
                            'campana_id' => $campana->id,
                            'envio_id' => $envio->id,
                        ]);

                        $proximaHora = $ahora->copy()->addHour()->startOfHour();
                        $this->release(max(60, $ahora->diffInSeconds($proximaHora)));

                        return;
                    }

                    if ($enviosGlobalDia >= 2000) {
                        Log::info('Límite diario de emails (SMTP global) alcanzado. Se reintentará mañana.', [
                            'campana_id' => $campana->id,
                            'envio_id' => $envio->id,
                        ]);

                        $proximoDia = $ahora->copy()->addDay()->startOfDay()->addMinutes(10);
                        $this->release($ahora->diffInSeconds($proximoDia));

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

                if ($usaEmailPersonalizado && $claveCuotaTenantDia) {
                    Cache::increment($claveCuotaTenantDia);
                }

                if (! $usaEmailPersonalizado) {
                    if ($claveCuotaGlobalHora) {
                        Cache::increment($claveCuotaGlobalHora);
                    }

                    if ($claveCuotaGlobalDia) {
                        Cache::increment($claveCuotaGlobalDia);
                    }
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
                self::dispatch($envio->id, $tenantModel->id)->delay(now()->addMinutes(5));
            }
        } finally {
            DB::purge('tenant');
            DB::setDefaultConnection('mysql');
            $campana->marcarCompletadaSiCorresponde();
        }
    }
}
