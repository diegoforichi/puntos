<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Cliente;
use App\Models\Usuario;
use App\Models\Factura;
use App\Models\PuntosCanjeado;
use App\Models\Actividad;
use App\Models\Configuracion;
use App\Services\NotificacionService;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Controlador de Canje de Puntos
 * 
 * Funcionalidades:
 * - Mostrar formulario de canje
 * - Buscar cliente por documento
 * - Validar puntos disponibles
 * - Autorizar canje (admin/supervisor directo, operario con contraseña)
 * - Procesar canje (actualizar puntos, eliminar facturas, registrar)
 * - Generar cupón de canje
 */
class PuntosController extends Controller
{
    /**
     * Mostrar formulario de canje de puntos
     * 
     * GET /{tenant}/puntos/canjear
     */
    public function mostrarFormulario(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        // Verificar permisos
        if (!in_array($usuario->rol, ['admin', 'supervisor'])) {
            return redirect("/{$tenant->rut}/dashboard")
                ->with('error', 'No tiene permisos para canjear puntos');
        }
        
        // Si viene cliente_id desde URL, pre-cargar sus datos
        $clienteId = $request->get('cliente_id');
        $cliente = null;
        
        if ($clienteId) {
            $cliente = Cliente::with('facturasActivas')->find($clienteId);
        }
        
        return view('puntos.canjear', [
            'tenant' => $tenant,
            'usuario' => $usuario,
            'cliente' => $cliente,
        ]);
    }

    /**
     * Buscar cliente para canje (formulario PHP)
     * 
     * POST /{tenant}/puntos/buscar-cliente
     */
    public function buscarCliente(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        
        $validated = $request->validate([
            'documento' => 'required|string',
        ]);
        
        $cliente = Cliente::where('documento', $validated['documento'])->first();
        
        if (!$cliente) {
            return redirect("/{$tenant->rut}/puntos/canjear")
                ->withInput()
                ->with('error_busqueda', 'Cliente no encontrado con documento: ' . $validated['documento']);
        }
        
        return redirect("/{$tenant->rut}/puntos/canjear?cliente_id={$cliente->id}");
    }

    /**
     * Procesar canje de puntos
     * 
     * POST /{tenant}/puntos/canjear
     */
    public function procesar(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        // Validar permisos
        if (!in_array($usuario->rol, ['admin', 'supervisor'])) {
            return back()->with('error', 'No tiene permisos para canjear puntos');
        }
        
        // Validar datos del formulario
        $validated = $request->validate([
            'cliente_id' => 'required|integer|exists:clientes,id',
            'puntos_a_canjear' => 'required|numeric|min:0.01',
            'concepto' => 'nullable|string|max:255',
        ], [
            'cliente_id.required' => 'Debe seleccionar un cliente',
            'cliente_id.exists' => 'Cliente no válido',
            'puntos_a_canjear.required' => 'Debe ingresar la cantidad de puntos',
            'puntos_a_canjear.min' => 'La cantidad debe ser mayor a 0',
        ]);
        
        // Buscar cliente
        $cliente = Cliente::findOrFail($validated['cliente_id']);
        
        // Validar que tenga puntos suficientes
        if (!$cliente->tienePuntosSuficientes($validated['puntos_a_canjear'])) {
            return back()
                ->withInput()
                ->with('error', "El cliente solo tiene {$cliente->puntos_formateados} puntos disponibles");
        }
        
        // Iniciar transacción
        DB::beginTransaction();
        
        try {
            // Calcular puntos restantes
            $puntosRestantes = $cliente->puntos_acumulados - $validated['puntos_a_canjear'];
            
            // Registrar canje
            $canje = PuntosCanjeado::create([
                'cliente_id' => $cliente->id,
                'puntos_canjeados' => $validated['puntos_a_canjear'],
                'puntos_restantes' => $puntosRestantes,
                'concepto' => $validated['concepto'] ?? 'Canje de puntos',
                'autorizado_por' => $usuario->id,
            ]);
            
            // Eliminar facturas de referencia (FIFO - First In, First Out)
            $this->eliminarFacturasReferencia($cliente, $validated['puntos_a_canjear']);
            
            // Actualizar puntos del cliente
            $cliente->update([
                'puntos_acumulados' => $puntosRestantes,
                'ultima_actividad' => now(),
            ]);
            
            // Registrar actividad
            Actividad::registrar(
                $usuario->id,
                Actividad::ACCION_CANJE,
                "Canje de {$validated['puntos_a_canjear']} puntos para {$cliente->nombre}",
                [
                    'cliente_id' => $cliente->id,
                    'puntos_canjeados' => $validated['puntos_a_canjear'],
                    'canje_id' => $canje->id,
                ]
            );
            
            DB::commit();
            
            // Notificar al cliente por WhatsApp
            $notificaciones = new NotificacionService($tenant);
            $notificaciones->notificarCanje(
                $cliente->toArray(),
                $validated['puntos_a_canjear'],
                $puntosRestantes
            );
            
            // Redirigir a página de confirmación/cupón
            return redirect("/{$tenant->rut}/puntos/cupon/{$canje->id}")
                ->with('success', 'Canje realizado exitosamente');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Error al procesar el canje: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar cupón de canje generado
     * 
     * GET /{tenant}/puntos/cupon/{id}
     */
    public function mostrarCupon(Request $request, $tenantRut, $canjeId)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        // Buscar canje con relaciones
        $canje = PuntosCanjeado::with(['cliente', 'autorizadoPor'])
            ->findOrFail($canjeId);
        
        return view('puntos.cupon', [
            'tenant' => $tenant,
            'usuario' => $usuario,
            'canje' => $canje,
        ]);
    }

    public function descargarCuponPdf(Request $request, $tenantRut, $canjeId)
    {
        $tenant = $request->attributes->get('tenant');

        $canje = PuntosCanjeado::with(['cliente', 'autorizadoPor'])
            ->findOrFail($canjeId);

        $contacto = Configuracion::getContacto();

        $pdf = Pdf::loadView('puntos.cupon_pdf', [
            'tenant' => $tenant,
            'canje' => $canje,
            'contacto' => $contacto,
            'generadoEn' => now(),
        ])->setPaper('a4');

        $nombreArchivo = 'cupon_' . $canje->codigo_cupon . '.pdf';

        return $pdf->stream($nombreArchivo);
    }

    /**
     * Eliminar facturas de referencia según puntos canjeados (FIFO)
     * 
     * @param Cliente $cliente
     * @param float $puntosACanjear
     * @return void
     */
    private function eliminarFacturasReferencia($cliente, $puntosACanjear)
    {
        // Obtener facturas activas ordenadas por fecha de emisión (FIFO)
        $facturas = $cliente->facturas()
            ->activas()
            ->orderBy('fecha_emision', 'asc')
            ->get();
        
        $puntosRestantesPorEliminar = $puntosACanjear;
        
        foreach ($facturas as $factura) {
            if ($puntosRestantesPorEliminar <= 0) {
                break;
            }
            
            if ($factura->puntos_generados <= $puntosRestantesPorEliminar) {
                // Eliminar factura completa
                $puntosRestantesPorEliminar -= $factura->puntos_generados;
                $factura->delete();
            } else {
                // Actualizar puntos de la factura (canje parcial)
                $nuevos_puntos = $factura->puntos_generados - $puntosRestantesPorEliminar;
                $factura->update(['puntos_generados' => $nuevos_puntos]);
                $puntosRestantesPorEliminar = 0;
            }
        }
    }
}
