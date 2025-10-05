<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promocion;
use App\Models\Actividad;

/**
 * Controlador de Promociones
 * 
 * Gestiona el CRUD de promociones:
 * - Listar con filtros
 * - Crear nueva promoción
 * - Editar promoción existente
 * - Activar/Desactivar
 * - Eliminar (soft delete)
 */
class PromocionController extends Controller
{
    /**
     * Listar promociones
     * 
     * GET /{tenant}/promociones
     */
    public function index(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        // Filtros
        $estado = $request->get('estado'); // activa, inactiva, todas
        $tipo = $request->get('tipo');
        $buscar = $request->get('buscar');
        
        // Query base
        $query = Promocion::query();
        
        // Aplicar filtros
        if ($estado === 'activa') {
            $query->activas();
        } elseif ($estado === 'inactiva') {
            $query->where('activa', false);
        }
        
        if ($tipo) {
            $query->porTipo($tipo);
        }
        
        if ($buscar) {
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }
        
        // Ordenar
        $promociones = $query->orderBy('prioridad', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('promociones.index', [
            'tenant' => $tenant,
            'usuario' => $usuario,
            'promociones' => $promociones,
            'filtros' => [
                'estado' => $estado,
                'tipo' => $tipo,
                'buscar' => $buscar,
            ],
        ]);
    }

    /**
     * Mostrar formulario de creación
     * 
     * GET /{tenant}/promociones/crear
     */
    public function create(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        return view('promociones.crear', [
            'tenant' => $tenant,
            'usuario' => $usuario,
        ]);
    }

    /**
     * Guardar nueva promoción
     * 
     * POST /{tenant}/promociones
     */
    public function store(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        // Validar datos
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'tipo' => 'required|in:descuento,bonificacion,multiplicador',
            'valor' => 'required|numeric|min:0',
            'condiciones' => 'nullable|json',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'prioridad' => 'nullable|integer|min:0|max:100',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'tipo.required' => 'Debe seleccionar un tipo de promoción',
            'tipo.in' => 'Tipo de promoción no válido',
            'valor.required' => 'El valor es obligatorio',
            'valor.min' => 'El valor debe ser mayor o igual a 0',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la de inicio',
        ]);
        
        // Procesar condiciones
        $condiciones = $this->procesarCondiciones($request);
        
        // Crear promoción
        $promocion = Promocion::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'tipo' => $validated['tipo'],
            'valor' => $validated['valor'],
            'condiciones' => $condiciones,
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'prioridad' => $validated['prioridad'] ?? 50,
            'activa' => $request->has('activa'),
        ]);
        
        // Registrar actividad
        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_PROMOCION,
            "Promoción creada: {$promocion->nombre}",
            ['promocion_id' => $promocion->id, 'accion' => 'crear']
        );
        
        return redirect("/{$tenant->rut}/promociones")
            ->with('success', 'Promoción creada exitosamente');
    }

    /**
     * Mostrar formulario de edición
     * 
     * GET /{tenant}/promociones/{id}/editar
     */
    public function edit(Request $request, $tenantRut, $id)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        $promocion = Promocion::findOrFail($id);
        
        return view('promociones.editar', [
            'tenant' => $tenant,
            'usuario' => $usuario,
            'promocion' => $promocion,
        ]);
    }

    /**
     * Actualizar promoción
     * 
     * PUT /{tenant}/promociones/{id}
     */
    public function update(Request $request, $tenantRut, $id)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        $promocion = Promocion::findOrFail($id);
        
        // Validar datos
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'tipo' => 'required|in:descuento,bonificacion,multiplicador',
            'valor' => 'required|numeric|min:0',
            'condiciones' => 'nullable|json',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'prioridad' => 'nullable|integer|min:0|max:100',
        ]);
        
        // Procesar condiciones
        $condiciones = $this->procesarCondiciones($request);
        
        // Actualizar
        $promocion->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'tipo' => $validated['tipo'],
            'valor' => $validated['valor'],
            'condiciones' => $condiciones,
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'prioridad' => $validated['prioridad'] ?? 50,
            'activa' => $request->has('activa'),
        ]);
        
        // Registrar actividad
        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_PROMOCION,
            "Promoción actualizada: {$promocion->nombre}",
            ['promocion_id' => $promocion->id, 'accion' => 'actualizar']
        );
        
        return redirect("/{$tenant->rut}/promociones")
            ->with('success', 'Promoción actualizada exitosamente');
    }

    /**
     * Cambiar estado (activar/desactivar)
     * 
     * POST /{tenant}/promociones/{id}/toggle
     */
    public function toggle(Request $request, $tenantRut, $id)
    {
        $usuario = $request->attributes->get('usuario');
        $promocion = Promocion::findOrFail($id);
        
        $promocion->activa = !$promocion->activa;
        $promocion->save();
        
        $estado = $promocion->activa ? 'activada' : 'desactivada';
        
        // Registrar actividad
        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_PROMOCION,
            "Promoción {$estado}: {$promocion->nombre}",
            ['promocion_id' => $promocion->id, 'accion' => 'toggle']
        );
        
        return back()->with('success', "Promoción {$estado} exitosamente");
    }

    /**
     * Eliminar promoción (soft delete)
     * 
     * DELETE /{tenant}/promociones/{id}
     */
    public function destroy(Request $request, $tenantRut, $id)
    {
        $usuario = $request->attributes->get('usuario');
        $promocion = Promocion::findOrFail($id);
        
        $nombre = $promocion->nombre;
        $promocion->delete();
        
        // Registrar actividad
        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_PROMOCION,
            "Promoción eliminada: {$nombre}",
            ['promocion_id' => $id, 'accion' => 'eliminar']
        );
        
        return redirect()->back()->with('success', 'Promoción eliminada exitosamente');
    }

    /**
     * Procesar condiciones del formulario
     * 
     * @param Request $request
     * @return array
     */
    private function procesarCondiciones(Request $request)
    {
        $condiciones = [];
        
        // Monto mínimo
        if ($request->filled('monto_minimo')) {
            $condiciones['monto_minimo'] = (float) $request->input('monto_minimo');
        }
        
        // Días de la semana
        if ($request->filled('dias_semana')) {
            $condiciones['dias_semana'] = $request->input('dias_semana');
        }
        
        // Clientes específicos
        if ($request->filled('clientes_especificos')) {
            $condiciones['clientes_especificos'] = explode(',', $request->input('clientes_especificos'));
        }
        
        return $condiciones;
    }
}
