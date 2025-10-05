<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use App\Services\PuntosService;
use App\Adapters\EfacturaAdapter;
use App\DTOs\StandardInvoiceDTO;
use App\Contracts\InvoiceAdapter;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    public function ingest(Request $request)
    {
        $payload = $request->json()->all();

        if (!$request->bearerToken()) {
            return $this->errorResponse('Falta header Authorization', Response::HTTP_UNAUTHORIZED);
        }

        $tenant = Tenant::where('api_key', $request->bearerToken())->first();

        if (!$tenant || !$tenant->isActivo()) {
            return $this->errorResponse('Tenant inválido o inactivo', Response::HTTP_UNAUTHORIZED);
        }

        try {
            $adapter = $this->obtenerAdaptador($tenant->formato_factura, $payload);
            if (!$adapter) {
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

    private function registrarWebhookGlobal(
        Tenant $tenant,
        string $estado,
        int $httpStatus,
        ?string $mensajeError,
        array $payload,
        ?array $result = null
    ): void
    {
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
        $adapter = new EfacturaAdapter();

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