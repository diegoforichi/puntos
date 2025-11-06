<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Cliente;
use App\Models\Tenant;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ClienteApiController extends Controller
{
    public function show(Request $request, string $tenantRut, string $documento)
    {
        $cliente = Cliente::where('documento', $documento)->first();

        if (! $cliente) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cliente no encontrado',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => 'success',
            'documento' => $cliente->documento,
            'nombre' => $cliente->nombre,
            'puntos_acumulados' => round((float) $cliente->puntos_acumulados, 2),
            'puntos_formateados' => (int) floor($cliente->puntos_acumulados),
            'ultima_actividad' => optional($cliente->ultima_actividad)->toDateTimeString(),
        ]);
    }

    public function canjear(Request $request, string $tenantRut, string $documento)
    {
        $payload = json_decode($request->getContent(), true);
        if (! is_array($payload)) {
            $payload = $request->all();
        }

        $data = validator($payload, [
            'puntos_a_canjear' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:255',
            'referencia' => 'nullable|string|max:255',
        ])->validate();

        $cliente = Cliente::where('documento', $documento)->first();

        if (! $cliente) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cliente no encontrado',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($cliente->puntos_acumulados < $data['puntos_a_canjear']) {
            return response()->json([
                'status' => 'error',
                'message' => 'El cliente no tiene puntos suficientes',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return DB::connection('tenant')->transaction(function () use ($cliente, $data, $documento, $request) {
            $puntosAnteriores = $cliente->puntos_acumulados;
            $puntosCanjeados = (float) $data['puntos_a_canjear'];
            $puntosRestantes = $puntosAnteriores - $puntosCanjeados;

            $ahora = date('Y-m-d H:i:s');

            DB::connection('tenant')->table('puntos_canjeados')->insert([
                'cliente_id' => $cliente->id,
                'puntos_canjeados' => $puntosCanjeados,
                'puntos_restantes' => $puntosRestantes,
                'concepto' => $data['descripcion'] ?? 'Canje API',
                'autorizado_por' => 'api',
                'origen' => 'api',
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            DB::connection('tenant')->table('clientes')->where('id', $cliente->id)->update([
                'puntos_acumulados' => $puntosRestantes,
                'ultima_actividad' => $ahora,
                'updated_at' => $ahora,
            ]);

            $tenant = $request->attributes->get('tenant');
            if ($tenant instanceof Tenant) {
                $notificaciones = new NotificacionService($tenant);
                $notificaciones->notificarCanje(
                    [
                        'nombre' => $cliente->nombre,
                        'telefono' => $cliente->telefono,
                    ],
                    $puntosCanjeados,
                    $puntosRestantes
                );
            }

            Actividad::registrar(null, Actividad::ACCION_CANJE, "Canje API por {$documento}", [
                'documento' => $documento,
                'puntos_canjeados' => $puntosCanjeados,
                'referencia' => $data['referencia'] ?? null,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'success',
                'mensaje' => 'Canje realizado con éxito',
                'puntos_anteriores' => round((float) $puntosAnteriores, 2),
                'puntos_canjeados' => round((float) $puntosCanjeados, 2),
                'puntos_nuevos' => round((float) $puntosRestantes, 2),
                'referencia' => $data['referencia'] ?? $data['descripcion'] ?? null,
            ]);
        });
    }
}
