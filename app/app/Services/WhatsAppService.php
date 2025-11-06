<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Enviar mensaje de WhatsApp usando configuración resuelta previamente
     */
    public static function enviar(array $config, string $telefono, string $mensaje, ?Tenant $tenant = null): array
    {
        if (! ($config['usar_canal'] ?? false)) {
            return [
                'success' => false,
                'message' => 'Canal de WhatsApp desactivado',
            ];
        }

        if (! ($config['activo'] ?? false)) {
            return [
                'success' => false,
                'message' => 'Servicio de WhatsApp deshabilitado',
            ];
        }

        if (empty($config['url']) || empty($config['token'])) {
            return [
                'success' => false,
                'message' => 'Configuración de WhatsApp incompleta',
            ];
        }

        try {
            if (! self::esTelefonoValido($telefono)) {
                Log::warning('Número de WhatsApp inválido detectado y omitido.', [
                    'telefono' => $telefono,
                    'tenant' => $tenant?->rut,
                ]);

                return [
                    'success' => false,
                    'message' => 'Número de WhatsApp inválido',
                ];
            }

            $telefonoLimpio = self::limpiarTelefono($telefono);

            $urlConParams = $config['url']
                .'?token='.urlencode($config['token'])
                .'&number='.urlencode($telefonoLimpio)
                .'&message='.urlencode($mensaje)
                .'&urlDocument=';

            $response = Http::timeout(10)->get($urlConParams);

            if (! $response->successful()) {
                throw new \RuntimeException('HTTP '.$response->status().': '.$response->body());
            }

            self::registrarEnvio($telefono, $mensaje, 'exitoso', $tenant);

            return [
                'success' => true,
                'message' => 'Mensaje enviado correctamente',
                'response' => $response->json(),
            ];
        } catch (\Throwable $e) {
            self::registrarEnvio($telefono, $mensaje, 'fallido: '.$e->getMessage(), $tenant);

            Log::error('Error enviando WhatsApp', [
                'telefono' => $telefono,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private static function limpiarTelefono(string $telefono): string
    {
        $limpio = preg_replace('/[^0-9]/', '', $telefono);

        if (preg_match('/^09\d{7}$/', $limpio)) {
            $limpio = '598'.substr($limpio, 1);
        }

        return $limpio;
    }

    private static function esTelefonoValido(string $telefonoOriginal): bool
    {
        $soloDigitos = preg_replace('/[^0-9]/', '', $telefonoOriginal);

        if (! $soloDigitos) {
            return false;
        }

        if (strlen($soloDigitos) < 8) {
            return false;
        }

        if (preg_match('/^(\d)\1+$/', $soloDigitos)) {
            return false;
        }

        return true;
    }

    private static function registrarEnvio(string $telefono, string $mensaje, string $estado, ?Tenant $tenant = null): void
    {
        if (! $tenant) {
            return;
        }

        try {
            $sqlitePath = $tenant->getSqlitePath();
            config([
                'database.connections.tenant_log' => [
                    'driver' => 'sqlite',
                    'database' => $sqlitePath,
                    'prefix' => '',
                ],
            ]);

            DB::connection('tenant_log')->table('whatsapp_logs')->insert([
                'telefono' => $telefono,
                'mensaje' => $mensaje,
                'estado' => $estado,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::purge('tenant_log');
        } catch (\Throwable $e) {
            Log::warning('No se pudo registrar envío de WhatsApp en tenant log', [
                'tenant' => $tenant->rut,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
