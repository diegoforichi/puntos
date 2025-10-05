<?php

namespace App\Services;

use App\Models\SystemConfig;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WhatsAppService
{
    /**
     * Enviar mensaje de WhatsApp usando la configuración global
     */
    public static function enviar(string $telefono, string $mensaje, ?Tenant $tenant = null): array
    {
        $config = SystemConfig::getWhatsAppConfig();

        if (!($config['activo'] ?? false)) {
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
            $telefonoLimpio = self::limpiarTelefono($telefono);
            
            // Construir URL con parámetros (igual que Google Apps Script)
            $urlConParams = $config['url'] 
                . '?token=' . urlencode($config['token'])
                . '&number=' . urlencode($telefonoLimpio)
                . '&message=' . urlencode($mensaje)
                . '&urlDocument='; // vacío por ahora, para futuros adjuntos

            $response = Http::timeout(10)->get($urlConParams);

            if (!$response->successful()) {
                throw new \RuntimeException('HTTP ' . $response->status() . ': ' . $response->body());
            }

            self::registrarEnvio($telefono, $mensaje, 'exitoso', $tenant);

            return [
                'success' => true,
                'message' => 'Mensaje enviado correctamente',
                'response' => $response->json(),
            ];

        } catch (\Throwable $e) {
            self::registrarEnvio($telefono, $mensaje, 'fallido: ' . $e->getMessage(), $tenant);

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

    /**
     * Limpiar y normalizar teléfono (formato internacional UY)
     */
    private static function limpiarTelefono(string $telefono): string
    {
        // Quitar espacios, guiones, paréntesis, +
        $limpio = preg_replace('/[^0-9]/', '', $telefono);
        
        // Si empieza con 09 y tiene 9 dígitos (formato local UY), convertir a internacional
        if (preg_match('/^09\d{7}$/', $limpio)) {
            $limpio = '598' . substr($limpio, 1); // quita el 0 inicial y agrega 598
        }
        
        return $limpio;
    }

    /**
     * Registrar envío en log del tenant
     */
    private static function registrarEnvio(string $telefono, string $mensaje, string $estado, ?Tenant $tenant): void
    {
        if (!$tenant) {
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

