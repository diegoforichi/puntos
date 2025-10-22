<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Configuracion;
use Carbon\Carbon;

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

                    $stats = $this->calcularStats($cliente, $facturasActivas);

                    $detalle = [
                        'cliente' => $cliente,
                        'facturas' => $facturasActivas,
                        'stats' => $stats,
                        'mensaje' => $resultado['mensaje'] ?? null,
                    ];
                } else {
                    session()->forget($sessionKey);
                    $resultado = null;
                }
            }
        } else {
            $resultado = null;
        }

        $contacto = Configuracion::getContacto();
        $temaColores = Configuracion::getTemaColores();

        return view('autoconsulta.index', [
            'tenant' => $tenant,
            'contacto' => $contacto,
            'nombreComercio' => $tenant->nombre_comercial,
            'resultado' => $resultado,
            'detalle' => $detalle,
            'documento' => $resultado['documento'] ?? null,
            'tema' => $temaColores,
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
        $proximaExpiracionRaw = DB::connection('tenant')
            ->table('facturas')
            ->where('cliente_id', $cliente->id)
            ->where('puntos_disponibles', '>', 0)
            ->where('fecha_vencimiento', '>=', date('Y-m-d 00:00:00'))
            ->orderBy('fecha_vencimiento', 'asc')
            ->value('fecha_vencimiento');

        $proximaExpiracion = $proximaExpiracionRaw
            ? Carbon::parse($proximaExpiracionRaw, 'America/Montevideo')
            : null;

        return [
            'puntos_disponibles' => $cliente->puntos_acumulados,
            'puntos_formateados' => $cliente->puntos_formateados,
            'total_facturas' => $facturasActivas->count(),
            'proxima_expiracion' => $proximaExpiracion,
        ];
    }
}
