<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Configuracion;

/**
 * Controlador de Autoconsulta Pública
 *
 * Portal público donde los clientes pueden consultar sus puntos
 * sin necesidad de autenticación
 *
 * Funcionalidades:
 * - Mostrar formulario de consulta
 * - Buscar cliente por documento
 * - Mostrar puntos disponibles
 * - Mostrar facturas activas
 * - Capturar datos de contacto (opcional)
 */
class AutoconsultaController extends Controller
{
    /**
     * Mostrar formulario de autoconsulta
     *
     * GET /{tenant}/consulta
     */
    public function index(Request $request)
    {
        $tenant = $request->attributes->get('tenant');

        $sessionKey = $this->getSessionKey($tenant->rut);
        $resultado = session($sessionKey);
        $detalle = null;

        if ($resultado && ($resultado['tenant'] ?? null) === $tenant->rut) {
            if (!empty($resultado['encontrado']) && !empty($resultado['cliente_id'])) {
                $cliente = Cliente::find($resultado['cliente_id']);

                if ($cliente) {
                    $facturasActivas = $cliente->facturas()
                        ->activas()
                        ->orderBy('fecha_vencimiento', 'asc')
                        ->get();

                    $detalle = [
                        'cliente' => $cliente,
                        'facturas' => $facturasActivas,
                        'stats' => $this->calcularStats($cliente, $facturasActivas),
                        'mensaje' => $resultado['mensaje'] ?? null,
                    ];
                } else {
                    // Si el cliente ya no existe, limpiar sesión
                    session()->forget($sessionKey);
                    $resultado = null;
                }
            }
        } else {
            $resultado = null;
        }

        $contacto = Configuracion::getContacto();

        return view('autoconsulta.index', [
            'tenant' => $tenant,
            'contacto' => $contacto,
            'resultado' => $resultado,
            'detalle' => $detalle,
            'documento' => $resultado['documento'] ?? null,
        ]);
    }

    /**
     * Consultar puntos del cliente
     *
     * POST /{tenant}/consulta
     */
    public function consultar(Request $request)
    {
        $tenant = $request->attributes->get('tenant');

        $request->validate([
            'documento' => 'required|string|min:6|max:20',
        ], [
            'documento.required' => 'El documento es obligatorio',
            'documento.min' => 'El documento debe tener al menos 6 caracteres',
        ]);

        $documento = trim($request->input('documento'));
        $sessionKey = $this->getSessionKey($tenant->rut);

        $cliente = Cliente::where('documento', $documento)->first();

        if (!$cliente) {
            session([$sessionKey => [
                'tenant' => $tenant->rut,
                'encontrado' => false,
                'documento' => $documento,
            ]]);

            return redirect()->route('tenant.consulta', ['tenant' => $tenant->rut])
                ->with('info', 'No encontramos ningún cliente con ese documento.');
        }

        $facturasActivas = $cliente->facturas()
            ->activas()
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        $stats = $this->calcularStats($cliente, $facturasActivas);

        session([$sessionKey => [
            'tenant' => $tenant->rut,
            'encontrado' => true,
            'documento' => $documento,
            'cliente_id' => $cliente->id,
            'mensaje' => null,
        ]]);

        return redirect()->route('tenant.consulta', ['tenant' => $tenant->rut])
            ->with('success', "¡Hola {$cliente->nombre}! Estos son tus puntos disponibles.");
    }

    /**
     * Capturar datos de contacto del cliente (opcional)
     *
     * POST /{tenant}/consulta/actualizar-contacto
     */
    public function actualizarContacto(Request $request)
    {
        $tenant = $request->attributes->get('tenant');

        $validated = $request->validate([
            'cliente_id' => 'required|integer|exists:clientes,id',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ], [
            'email.email' => 'El email debe ser válido',
        ]);

        $cliente = Cliente::findOrFail($validated['cliente_id']);

        $updated = false;
        if (!empty($validated['telefono']) && $cliente->telefono !== $validated['telefono']) {
            $cliente->telefono = trim($validated['telefono']);
            $updated = true;
        }

        if (!empty($validated['email']) && $cliente->email !== $validated['email']) {
            $cliente->email = trim($validated['email']);
            $updated = true;
        }

        if ($updated) {
            $cliente->save();
        }

        $sessionKey = $this->getSessionKey($tenant->rut);
        session([$sessionKey => [
            'tenant' => $tenant->rut,
            'encontrado' => true,
            'documento' => $cliente->documento,
            'cliente_id' => $cliente->id,
            'mensaje' => $updated ? 'Tus datos de contacto han sido actualizados.' : 'Tus datos ya estaban registrados.',
        ]]);

        return redirect()->route('tenant.consulta', ['tenant' => $tenant->rut]);
    }

    private function getSessionKey(string $tenantRut): string
    {
        return "consulta.{$tenantRut}";
    }

    private function calcularStats(Cliente $cliente, $facturasActivas): array
    {
        return [
            'puntos_disponibles' => $cliente->puntos_acumulados,
            'puntos_formateados' => $cliente->puntos_formateados,
            'total_facturas' => $facturasActivas->count(),
            'facturas_por_vencer' => $cliente->facturas()->porVencer(30)->count(),
            'puntos_generados_total' => $cliente->facturas()->sum('puntos_generados'),
            'puntos_canjeados_total' => $cliente->puntosCanjeados()->sum('puntos_canjeados'),
        ];
    }
}
