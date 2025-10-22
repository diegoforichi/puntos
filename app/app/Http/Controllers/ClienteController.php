<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\PuntosCanjeado;
use App\Models\Actividad;

/**
 * Controlador de Gestión de Clientes
 * 
 * Funcionalidades:
 * - Listar clientes con búsqueda y paginación
 * - Ver detalle del cliente
 * - Ver historial de facturas
 * - Ver historial de canjes
 * - Editar datos básicos (nombre, teléfono, email, dirección)
 */
class ClienteController extends Controller
{
    /**
     * Listar todos los clientes con búsqueda y paginación
     * 
     * GET /{tenant}/clientes
     */
    public function index(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        // Obtener parámetros de búsqueda y filtros
        $search = $request->get('search');
        $filtro = $request->get('filtro', 'todos'); // todos, con_puntos, activos
        $ordenar = $request->get('ordenar', 'recientes'); // recientes, antiguos, puntos_desc, puntos_asc
        
        // Construir query
        $query = Cliente::query();
        
        // Aplicar búsqueda
        if ($search) {
            $query->buscar($search);
        }
        
        // Aplicar filtros
        switch ($filtro) {
            case 'con_puntos':
                $query->conPuntos();
                break;
            case 'activos':
                $query->activos(30);
                break;
        }
        
        // Aplicar ordenamiento
        switch ($ordenar) {
            case 'antiguos':
                $query->orderBy('created_at', 'asc');
                break;
            case 'puntos_desc':
                $query->orderBy('puntos_acumulados', 'desc');
                break;
            case 'puntos_asc':
                $query->orderBy('puntos_acumulados', 'asc');
                break;
            default: // recientes
                $query->orderBy('created_at', 'desc');
                break;
        }
        
        // Paginar resultados (10 por página para mejor visualización)
        $clientes = $query->paginate(10)->withQueryString();
        
        // Estadísticas para el header
        $stats = [
            'total' => Cliente::count(),
            'con_puntos' => Cliente::conPuntos()->count(),
            'activos' => Cliente::activos(30)->count(),
            'total_puntos' => Cliente::sum('puntos_acumulados'),
        ];
        
        return view('clientes.index', [
            'tenant' => $tenant,
            'usuario' => $usuario,
            'clientes' => $clientes,
            'stats' => $stats,
            'search' => $search,
            'filtro' => $filtro,
            'ordenar' => $ordenar,
        ]);
    }

    /**
     * Mostrar detalle del cliente
     * 
     * GET /{tenant}/clientes/{id}
     */
    public function show(Request $request, $tenantRut, $id)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        // Buscar cliente
        $cliente = Cliente::findOrFail($id);
        
        // Obtener facturas activas (no vencidas)
        $facturasActivas = $cliente->facturas()
            ->activas()
            ->orderBy('fecha_vencimiento', 'asc')
            ->limit(10)
            ->get();
        
        // Obtener historial de canjes
        $canjes = $cliente->puntosCanjeados()
            ->with('autorizadoPor:id,nombre')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Obtener puntos vencidos
        $puntosVencidos = $cliente->puntosVencidos()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Estadísticas del cliente
        $stats = [
            'total_facturas' => $cliente->facturas()->count(),
            'facturas_activas' => $facturasActivas->count(),
            'puntos_disponibles' => $cliente->puntos_acumulados,
            'puntos_generados_total' => $cliente->facturas()->sum('puntos_generados'),
            'puntos_canjeados_total' => $cliente->puntosCanjeados()->sum('puntos_canjeados'),
            'puntos_vencidos_total' => $cliente->puntosVencidos()->sum('puntos_vencidos'),
            'ultimo_canje' => $cliente->puntosCanjeados()->latest()->first()?->created_at,
        ];
        
        return view('clientes.show', [
            'tenant' => $tenant,
            'usuario' => $usuario,
            'cliente' => $cliente,
            'facturasActivas' => $facturasActivas,
            'canjes' => $canjes,
            'puntosVencidos' => $puntosVencidos,
            'stats' => $stats,
        ]);
    }

    /**
     * Mostrar formulario de edición
     * 
     * GET /{tenant}/clientes/{id}/editar
     */
    public function edit(Request $request, $tenantRut, $id)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        // Solo admin y supervisor pueden editar
        if (!in_array($usuario->rol, ['admin', 'supervisor'])) {
            return back()->with('error', 'No tiene permisos para editar clientes');
        }
        
        $cliente = Cliente::findOrFail($id);
        
        return view('clientes.edit', [
            'tenant' => $tenant,
            'usuario' => $usuario,
            'cliente' => $cliente,
        ]);
    }

    /**
     * Actualizar datos del cliente
     * 
     * PUT /{tenant}/clientes/{id}
     */
    public function update(Request $request, $tenantRut, $id)
    {
        $usuario = $request->attributes->get('usuario');
        
        // Solo admin y supervisor pueden editar
        if (!in_array($usuario->rol, ['admin', 'supervisor'])) {
            return back()->with('error', 'No tiene permisos para editar clientes');
        }
        
        $cliente = Cliente::findOrFail($id);
        
        // Validar datos
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:500',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'email.email' => 'El email debe ser válido',
        ]);
        
        // Actualizar cliente
        $cliente->update($validated);
        
        // Registrar actividad
        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_CLIENTE_CREADO,
            "Cliente {$cliente->nombre} actualizado",
            ['cliente_id' => $cliente->id]
        );
        
        return redirect("/{$tenantRut}/clientes/{$id}")
            ->with('success', 'Cliente actualizado correctamente');
    }

    /**
     * Ver historial completo de facturas del cliente
     * 
     * GET /{tenant}/clientes/{id}/facturas
     */
    public function facturas(Request $request, $tenantRut, $id)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        $cliente = Cliente::findOrFail($id);
        
        // Obtener todas las facturas paginadas
        $facturas = $cliente->facturas()
            ->orderBy('fecha_emision', 'desc')
            ->paginate(20)->withQueryString();
        
        return view('clientes.facturas', [
            'tenant' => $tenant,
            'usuario' => $usuario,
            'cliente' => $cliente,
            'facturas' => $facturas,
        ]);
    }

    /**
     * Búsqueda AJAX en tiempo real
     * 
     * GET /{tenant}/clientes/buscar
     */
    public function buscar(Request $request)
    {
        $search = $request->get('q');
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }
        
        $clientes = Cliente::buscar($search)
            ->select('id', 'documento', 'nombre', 'puntos_acumulados')
            ->limit(10)
            ->get();
        
        return response()->json($clientes);
    }
}
