<?php

namespace App\Http\Controllers\Api;

use App\Adapters\EfacturaAdapter;
use App\Contracts\InvoiceAdapter;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\PuntosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    public function ingest(Request $request)
    {
        $payload = $request->json()->all();

        if (! $request->bearerToken()) {
            return $this->errorResponse('Falta header Authorization', Response::HTTP_UNAUTHORIZED);
        }

        $tenant = Tenant::where('api_key', $request->bearerToken())->first();

        if (! $tenant || ! $tenant->isActivo()) {
            return $this->errorResponse('Tenant inválido o inactivo', Response::HTTP_UNAUTHORIZED);
        }

        try {
            $adapter = $this->obtenerAdaptador($tenant->formato_factura, $payload);
            if (! $adapter) {
                return $this->errorResponse('Formato de factura no soportado', Response::HTTP_BAD_REQUEST);
            }

            $dto = $adapter->toStandard($payload);

            $service = new PuntosService($tenant);
            $result = $service->procesarFactura($dto);

            $this->registrarWebhookGlobal(
                $tenant,
                $result['estado'] ?? 'procesado',
                200,
                $result['motivo_no_acumulo'] ?? null,
                $payload,
                $result
            );

            $tenant->ultimo_webhook = now();
            $tenant->facturas_recibidas = $tenant->facturas_recibidas + 1;
            $tenant->puntos_generados_total = $tenant->puntos_generados_total + ($result['puntos_generados'] ?? 0);
            $tenant->save();

            if (($result['estado'] ?? 'procesado') === 'omitido') {
                $mensaje = 'Factura registrada pero sin acumulación de puntos';
            } else {
                $mensaje = 'Factura procesada correctamente';
            }

            return response()->json([
                'status' => 'ok',
                'tenant' => $tenant->rut,
                'cliente' => $result['cliente'] ?? null,
                'puntos_generados' => $result['puntos_generados'] ?? 0,
                'estado' => $result['estado'] ?? 'procesado',
                'motivo_no_acumulo' => $result['motivo_no_acumulo'] ?? null,
                'mensaje' => $mensaje,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error procesando webhook', [
                'tenant' => $tenant->rut,
                'error' => $e->getMessage(),
            ]);

            $this->registrarWebhookGlobal($tenant, 'error', 500, $e->getMessage(), $payload);

            return $this->errorResponse('Error interno al procesar el webhook', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function cancel(Request $request)
    {
        $payload = $request->json()->all();

        if (! $request->bearerToken()) {
            return $this->errorResponse('Falta header Authorization', Response::HTTP_UNAUTHORIZED);
        }

        $tenant = Tenant::where('api_key', $request->bearerToken())->first();

        if (! $tenant || ! $tenant->isActivo()) {
            return $this->errorResponse('Tenant inválido o inactivo', Response::HTTP_UNAUTHORIZED);
        }

        try {
            $adapter = $this->obtenerAdaptador($tenant->formato_factura, $payload);
            if (! $adapter) {
                return $this->errorResponse('Formato de factura no soportado', Response::HTTP_BAD_REQUEST);
            }

            $dto = $adapter->toStandard($payload);
            $service = new PuntosService($tenant);

            $cliente = DB::connection('tenant')->table('clientes')
                ->where('documento', $dto->documentoCliente)
                ->first();

            if (! $cliente) {
                $this->registrarWebhookGlobal($tenant, 'error', 404, 'Cliente no encontrado para cancelación', $payload);

                return $this->errorResponse('Cliente no encontrado para la cancelación', Response::HTTP_NOT_FOUND);
            }

            $factura = DB::connection('tenant')->table('facturas')
                ->where('cliente_id', $cliente->id)
                ->where('numero_factura', $dto->numeroFactura)
                ->orderByDesc('id')
                ->first();

            if (! $factura) {
                $this->registrarWebhookGlobal($tenant, 'error', 404, 'Factura no encontrada para cancelación', $payload);

                return $this->errorResponse('Factura no encontrada para la cancelación', Response::HTTP_NOT_FOUND);
            }

            $cancelacion = DB::connection('tenant')->transaction(function () use (
                $service,
                $cliente,
                $factura,
                $payload
            ) {
                $delta = -1 * (float) $factura->puntos_generados;
                $ajuste = [
                    'saldo_anterior' => (float) $cliente->puntos_acumulados,
                    'saldo_nuevo' => (float) $cliente->puntos_acumulados,
                    'ajuste_aplicado' => 0.0,
                ];

                if ($delta !== 0.0) {
                    $ajuste = $service->ajustarSaldoCliente($cliente->id, $delta);
                }

                DB::connection('tenant')->table('facturas')->where('id', $factura->id)->delete();

                DB::connection('tenant')->table('webhook_inbox')->insert([
                    'estado' => 'procesado',
                    'cfe_id' => $factura->cfe_id,
                    'documento_cliente' => $cliente->documento,
                    'puntos_generados' => $ajuste['ajuste_aplicado'],
                    'motivo_no_acumulo' => 'factura_cancelada',
                    'origen' => 'efactura_cancel',
                    'mensaje_error' => null,
                    'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                    'procesado_en' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return [
                    'ajuste' => $ajuste,
                    'delta' => $ajuste['ajuste_aplicado'],
                    'puntos_originales' => (float) $factura->puntos_generados,
                ];
            });

            $tenant->ultimo_webhook = now();
            $tenant->facturas_recibidas = max(0, $tenant->facturas_recibidas - 1);
            $tenant->puntos_generados_total = round($tenant->puntos_generados_total + $cancelacion['delta'], 2);
            $tenant->save();

            $this->registrarWebhookGlobal(
                $tenant,
                'procesado',
                200,
                'Factura cancelada',
                $payload,
                [
                    'puntos_generados' => $cancelacion['delta'],
                    'motivo_no_acumulo' => 'factura_cancelada',
                ]
            );

            return response()->json([
                'status' => 'ok',
                'tenant' => $tenant->rut,
                'cliente_documento' => $cliente->documento,
                'numero_factura' => $factura->numero_factura,
                'puntos_revertidos' => $cancelacion['ajuste']['ajuste_aplicado'],
                'saldo_actual' => $cancelacion['ajuste']['saldo_nuevo'],
                'mensaje' => 'Factura cancelada y puntos ajustados',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error procesando cancelación de factura', [
                'tenant' => $tenant->rut,
                'error' => $e->getMessage(),
            ]);

            $this->registrarWebhookGlobal($tenant, 'error', 500, $e->getMessage(), $payload);

            return $this->errorResponse('Error interno al procesar la cancelación', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function registrarWebhookGlobal(
        Tenant $tenant,
        string $estado,
        int $httpStatus,
        ?string $mensajeError,
        array $payload,
        ?array $result = null
    ): void {
        DB::connection('mysql')->table('webhook_inbox_global')->insert([
            'tenant_rut' => $tenant->rut,
            'estado' => $estado,
            'origen' => 'efactura',
            'http_status' => $httpStatus,
            'mensaje_error' => $mensajeError,
            'payload_json' => json_encode($payload, JSON_PRETTY_PRINT),
            'cfe_id' => $payload['CfeId'] ?? null,
            'documento_cliente' => $payload['Client']['NroDoc'] ?? null,
            'puntos_generados' => $result['puntos_generados'] ?? null,
            'motivo_no_acumulo' => $result['motivo_no_acumulo'] ?? null,
            'procesado_en' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function obtenerAdaptador(string $formato, array $payload): ?InvoiceAdapter
    {
        $adapter = new EfacturaAdapter;

        if ($adapter->matches($payload)) {
            return $adapter;
        }

        return null;
    }

    private function errorResponse(string $message, int $status)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $status);
    }
}
