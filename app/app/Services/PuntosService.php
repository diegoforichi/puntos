<?php

namespace App\Services;

use App\DTOs\StandardInvoiceDTO;
use App\Models\Configuracion;
use App\Models\Promocion;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificacionService;

/**
 * Servicio para gestionar puntos de clientes
 */
class PuntosService
{
    private Tenant $tenant;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
        $this->configurarConexionTenant();
    }

    /**
     * Configurar conexión a la base de datos del tenant
     */
    private function configurarConexionTenant(): void
    {
        $sqlitePath = $this->tenant->getSqlitePath();
        
        config([
            'database.connections.tenant' => [
                'driver' => 'sqlite',
                'database' => $sqlitePath,
                'prefix' => '',
                'foreign_key_constraints' => false,
            ]
        ]);
    }

    /**
     * Procesar factura y generar puntos
     */
    public function procesarFactura(StandardInvoiceDTO $factura): array
    {
        try {
            $cliente = $this->obtenerOCrearCliente(
                $factura->documentoCliente,
                $factura->nombreCliente,
                $factura->telefonoCliente,
                $factura->emailCliente
            );

            $configuracion = $this->obtenerConfiguracion();
            $puntosPorPesos = $configuracion['puntos_por_pesos'];
            $diasVencimiento = $configuracion['dias_vencimiento'];
            $monedaBase = $configuracion['moneda_base'];
            $tasaUsd = $configuracion['tasa_usd'];
            $modoMonedaDesconocida = $configuracion['moneda_desconocida'];
            $excluirEfacturas = $configuracion['acumulacion_excluir_efacturas'];

            $permitidoAcumular = $this->permitidoAcumular($factura, $excluirEfacturas);
            $motivoNoAcumulo = $permitidoAcumular ? null : $this->motivoNoAcumulo($factura, $excluirEfacturas);

            $conversion = $this->convertirMontoSegunMoneda($factura, $monedaBase, $tasaUsd, $modoMonedaDesconocida);

            if ($conversion['omitido']) {
                $permitidoAcumular = false;
                $motivoNoAcumulo = 'moneda_sin_tasa';
            }

            $puntosBase = $permitidoAcumular ? round($conversion['monto'] / $puntosPorPesos, 2) : 0;
            $resultadoPromocion = $this->aplicarPromocion($factura, $puntosBase, $cliente);
            $promocionAplicada = $resultadoPromocion['promocion_id'];
            $puntosFinales = $permitidoAcumular ? $resultadoPromocion['puntos_finales'] : 0;
            $signo = $this->signoPorCfeId($factura->cfeId);
            $puntosFinales *= $signo;

            $fechaVencimiento = now()->addDays($diasVencimiento);

            $facturaData = [
                'cliente_id' => $cliente['id'],
                'numero_factura' => $factura->numeroFactura,
                'monto_total' => $factura->montoTotal,
                'moneda' => $factura->moneda,
                'puntos_generados' => $puntosFinales,
                'promocion_aplicada' => $promocionAplicada,
                'payload_json' => json_encode($factura->payloadOriginal, JSON_UNESCAPED_UNICODE),
                'fecha_emision' => $factura->fechaEmision,
                'fecha_vencimiento' => $fechaVencimiento,
                'created_at' => now(),
                'updated_at' => now()
            ];

            $facturaDataExtendida = $facturaData + [
                'cfe_id' => $factura->cfeId,
                'acumulo' => $permitidoAcumular,
                'motivo_no_acumulo' => $motivoNoAcumulo,
            ];

            try {
                $facturaId = DB::connection('tenant')->table('facturas')->insertGetId($facturaDataExtendida);
            } catch (\Throwable $e) {
                if ($this->faltaColumna($e)) {
                    Log::warning('Columnas extendidas no presentes en facturas, insertando mínimo', [
                        'tenant' => $this->tenant->rut,
                        'error' => $e->getMessage(),
                    ]);
                    $facturaId = DB::connection('tenant')->table('facturas')->insertGetId($facturaData);
                } else {
                    throw $e;
                }
            }

            if ($permitidoAcumular && $puntosFinales !== 0) {
                DB::connection('tenant')->table('clientes')
                    ->where('id', $cliente['id'])
                    ->update([
                        'puntos_acumulados' => DB::raw('puntos_acumulados + ' . $puntosFinales),
                        'ultima_actividad' => now(),
                        'updated_at' => now(),
                    ]);

                $cliente['puntos_acumulados'] += $puntosFinales;
            }

            $this->registrarActividad('factura_procesada', "Factura {$factura->numeroFactura} procesada", [
                'cliente_documento' => $cliente['documento'],
                'puntos_generados' => $puntosFinales,
                'monto' => $factura->montoTotal,
                'acumulo' => $permitidoAcumular,
                'motivo_no_acumulo' => $motivoNoAcumulo,
            ]);

            $inboxData = [
                'estado' => $permitidoAcumular ? 'procesado' : 'omitido',
                'origen' => 'efactura',
                'payload_json' => json_encode($factura->payloadOriginal, JSON_UNESCAPED_UNICODE),
                'procesado_en' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $inboxDataExtendido = $inboxData + [
                'cfe_id' => $factura->cfeId,
                'documento_cliente' => $factura->documentoCliente,
                'puntos_generados' => $puntosFinales,
                'motivo_no_acumulo' => $motivoNoAcumulo,
            ];

            try {
                DB::connection('tenant')->table('webhook_inbox')->insert($inboxDataExtendido);
            } catch (\Throwable $e) {
                if ($this->faltaColumna($e)) {
                    Log::warning('Columnas extendidas no presentes en webhook_inbox, insertando mínimo', [
                        'tenant' => $this->tenant->rut,
                        'error' => $e->getMessage(),
                    ]);
                    DB::connection('tenant')->table('webhook_inbox')->insert($inboxData);
                } else {
                    throw $e;
                }
            }

            $puntosTotales = $permitidoAcumular
                ? ($cliente['puntos_acumulados'] + $puntosFinales)
                : $cliente['puntos_acumulados'];

            return [
                'success' => true,
                'cliente' => $cliente,
                'puntos_generados' => $puntosFinales,
                'puntos_totales' => $puntosTotales,
                'factura_id' => $facturaId,
                'estado' => $permitidoAcumular ? 'procesado' : 'omitido',
                'motivo_no_acumulo' => $motivoNoAcumulo,
            ];

        } catch (\Exception $e) {
            Log::error('Error procesando factura', [
                'error' => $e->getMessage(),
                'factura' => $factura->numeroFactura
            ]);

            throw $e;
        }
    }
    
    /**
     * Obtener o crear cliente
     */
    private function obtenerOCrearCliente(string $documento, string $nombre, ?string $telefono, ?string $email): array
    {
        $cliente = DB::connection('tenant')->table('clientes')->where('documento', $documento)->first();
        $telefonoNormalizado = $this->normalizarTelefonoLocal($telefono);

        if ($cliente) {
            $clienteData = (array) $cliente;

            $updates = [];
            if ($telefonoNormalizado && empty($cliente->telefono)) {
                $updates['telefono'] = $telefonoNormalizado;
                $clienteData['telefono'] = $telefonoNormalizado;
            }
            if ($email && !$cliente->email) {
                $updates['email'] = $email;
                $clienteData['email'] = $email;
            }

            if (!empty($updates)) {
                DB::connection('tenant')->table('clientes')->where('id', $cliente->id)->update($updates);
            }

            return $clienteData;
        }

        $clienteId = DB::connection('tenant')->table('clientes')->insertGetId([
            'documento' => $documento,
            'nombre' => $nombre,
            'telefono' => $telefonoNormalizado,
            'email' => $email,
            'puntos_acumulados' => 0,
            'ultima_actividad' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $nuevoCliente = [
            'id' => $clienteId,
            'documento' => $documento,
            'nombre' => $nombre,
            'telefono' => $telefonoNormalizado,
            'email' => $email,
            'puntos_acumulados' => 0
        ];

        $notificaciones = new NotificacionService($this->tenant);
        $notificaciones->notificarBienvenida($nuevoCliente);

        return $nuevoCliente;
    }
    
    /**
     * Obtener configuración del tenant
     */
    private function obtenerConfiguracion(): array
    {
        $puntosPorPesos = DB::connection('tenant')->table('configuracion')
            ->where('key', 'puntos_por_pesos')
            ->value('value');
        
        $diasVencimiento = DB::connection('tenant')->table('configuracion')
            ->where('key', 'dias_vencimiento')
            ->value('value');

        return [
            'puntos_por_pesos' => json_decode($puntosPorPesos, true)['valor'] ?? 100,
            'dias_vencimiento' => json_decode($diasVencimiento, true)['valor'] ?? 180,
            'acumulacion_excluir_efacturas' => Configuracion::getAcumulacionExcluirEfacturas(),
            'moneda_base' => Configuracion::getMonedaBase(),
            'tasa_usd' => Configuracion::getTasaUsd(),
            'moneda_desconocida' => Configuracion::getMonedaDesconocida(),
        ];
    }

    private function convertirMontoSegunMoneda(StandardInvoiceDTO $factura, string $monedaBase, float $tasaUsd, string $modoMonedaDesconocida): array
    {
        $monedaFactura = strtoupper($factura->moneda ?? '');
        $monedaBase = strtoupper($monedaBase);

        if ($monedaFactura === '' || $monedaFactura === $monedaBase) {
            return ['monto' => $factura->montoTotal, 'omitido' => false];
        }

        if ($monedaFactura === 'USD') {
            return ['monto' => $factura->montoTotal * $tasaUsd, 'omitido' => false];
        }

        if ($modoMonedaDesconocida === 'sin_convertir') {
            return ['monto' => $factura->montoTotal, 'omitido' => false];
        }

        return ['monto' => $factura->montoTotal, 'omitido' => true];
    }
    
    /**
     * Registrar actividad en el log
     */
    private function registrarActividad(string $accion, string $descripcion, array $datos = []): void
    {
        DB::connection('tenant')->table('actividades')->insert([
            'usuario_id' => null, // Por ahora null, luego será el usuario autenticado
            'accion' => $accion,
            'descripcion' => $descripcion,
            'datos_json' => json_encode($datos, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    /**
     * Aplicar promoción a la factura si corresponde
     * 
     * @param StandardInvoiceDTO $factura
     * @param float $puntosBase
     * @param array $cliente
     * @return array ['promocion_id' => int|null, 'puntos_finales' => float]
     */
    private function aplicarPromocion(StandardInvoiceDTO $factura, float $puntosBase, array $cliente): array
    {
        // Usar el método estático del modelo Promocion
        return Promocion::aplicar($factura->montoTotal, $puntosBase, $factura->fechaEmision, $cliente['id']);
    }

    private function permitidoAcumular(StandardInvoiceDTO $factura, bool $excluirEfacturas): bool
    {
        if ($excluirEfacturas && $this->esEfactura($factura)) {
            return false;
        }

        return true;
    }

    private function motivoNoAcumulo(StandardInvoiceDTO $factura, bool $excluirEfacturas): ?string
    {
        if ($excluirEfacturas && $this->esEfactura($factura)) {
            return 'excluir_efacturas';
        }

        return null;
    }

    private function esEfactura(StandardInvoiceDTO $factura): bool
    {
        return in_array($factura->cfeId, [111, 112, 113], true);
    }

    private function signoPorCfeId(?int $cfeId): int
    {
        if (in_array($cfeId, [102, 112], true)) {
            return -1;
        }

        return 1;
    }

    private function faltaColumna(\Throwable $e): bool
    {
        $mensaje = $e->getMessage();

        return str_contains($mensaje, 'no such column')
            || str_contains($mensaje, 'has no column named')
            || str_contains($mensaje, 'no column named');
    }

    private function normalizarTelefonoLocal(?string $telefono): ?string
    {
        if (!$telefono) {
            return null;
        }

        $soloNumeros = preg_replace('/[^0-9]/', '', $telefono);

        if (!$soloNumeros) {
            return null;
        }

        if (str_starts_with($soloNumeros, '598') && strlen($soloNumeros) >= 5) {
            $resto = substr($soloNumeros, 3);
            if ($resto !== '') {
                if ($resto[0] !== '0') {
                    $resto = '0' . $resto;
                }
                return substr($resto, 0, 9);
            }
        }

        if (strlen($soloNumeros) === 8 && $soloNumeros[0] === '9') {
            return '0' . $soloNumeros;
        }

        if (strlen($soloNumeros) === 9 && $soloNumeros[0] === '0') {
            return $soloNumeros;
        }

        return $soloNumeros;
    }
}
