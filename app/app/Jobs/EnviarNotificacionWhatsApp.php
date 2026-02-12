<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\NotificacionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnviarNotificacionWhatsApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const TIPO_CANJE = 'canje';

    public const TIPO_PROMOCION = 'promocion';

    public const TIPO_VENCIMIENTO = 'vencimiento';

    public const TIPO_BIENVENIDA = 'bienvenida';

    public function __construct(
        public int $tenantId,
        public string $tipo,
        public array $cliente,
        public array $datosExtra = []
    ) {
        $this->onQueue('campanas');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Delay aleatorio antes de enviar (3-6 segundos) para evitar bloqueos de WhatsApp
        usleep(random_int(3000000, 6000000));

        $tenant = Tenant::find($this->tenantId);

        if (! $tenant) {
            Log::warning('No se encontró el tenant para procesar notificación WhatsApp', [
                'tenant_id' => $this->tenantId,
            ]);

            return;
        }

        $sqlitePath = $tenant->getSqlitePath();
        $defaultConnection = DB::getDefaultConnection();

        Config::set('database.connections.tenant', [
            'driver' => 'sqlite',
            'database' => $sqlitePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        DB::purge('tenant');

        $notificaciones = new NotificacionService($tenant);

        try {
            switch ($this->tipo) {
                case self::TIPO_CANJE:
                    $notificaciones->notificarCanje(
                        $this->cliente,
                        (float) ($this->datosExtra['puntos_canjeados'] ?? 0),
                        (float) ($this->datosExtra['puntos_restantes'] ?? 0)
                    );
                    break;
                case self::TIPO_PROMOCION:
                    $notificaciones->notificarPromocion(
                        $this->cliente,
                        (string) ($this->datosExtra['descripcion'] ?? ''),
                        (string) ($this->datosExtra['fecha_fin'] ?? '')
                    );
                    break;
                case self::TIPO_VENCIMIENTO:
                    $notificaciones->notificarPuntosProximosAVencer(
                        $this->cliente,
                        (float) ($this->datosExtra['puntos'] ?? 0),
                        (string) ($this->datosExtra['fecha_vencimiento'] ?? '')
                    );
                    break;
                case self::TIPO_BIENVENIDA:
                    $notificaciones->notificarBienvenida($this->cliente);
                    break;
                default:
                    Log::warning('Tipo de notificación WhatsApp no soportado', [
                        'tipo' => $this->tipo,
                        'tenant' => $tenant->rut,
                    ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error enviando notificación WhatsApp asincrónica', [
                'tipo' => $this->tipo,
                'tenant' => $tenant->rut,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } finally {
            DB::purge('tenant');
            DB::setDefaultConnection($defaultConnection);
        }
    }
}
