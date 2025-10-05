<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Actividad;
use Illuminate\Support\Facades\Hash;

/**
 * Controlador de Usuarios
 * 
 * CRUD de usuarios del tenant:
 * - Listar
 * - Crear
 * - Editar
 * - Activar/Desactivar
 * - Cambiar contraseña
 */
class UsuarioController extends Controller
{
    /**
     * Listar usuarios
     * 
     * GET /{tenant}/usuarios
     */
    public function index(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        // Filtros
        $rol = $request->get('rol');
        $estado = $request->get('estado');
        
        // Query
        $query = Usuario::query();
        
        if ($rol) {
            $query->where('rol', $rol);
        }
        
        if ($estado === 'activos') {
            $query->where('activo', true);
        } elseif ($estado === 'inactivos') {
            $query->where('activo', false);
        }
        
        $usuarios = $query->orderBy('nombre')->get();
        
        return view('usuarios.index', [
            'tenant' => $tenant,
            'usuario' => $usuario,
            'usuarios' => $usuarios,
            'filtros' => [
                'rol' => $rol,
                'estado' => $estado,
            ],
        ]);
    }

    /**
     * Mostrar formulario de creación
     * 
     * GET /{tenant}/usuarios/crear
     */
    public function create(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        return view('usuarios.crear', [
            'tenant' => $tenant,
            'usuario' => $usuario,
        ]);
    }

    /**
     * Guardar nuevo usuario
     * 
     * POST /{tenant}/usuarios
     */
    public function store(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        // Validar
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:usuarios,email',
            'username' => 'required|string|max:100|unique:usuarios,username',
            'password' => 'required|string|min:6|confirmed',
            'rol' => 'required|in:admin,supervisor,operario',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'email.email' => 'El email debe ser válido',
            'email.unique' => 'Este email ya está registrado',
            'username.required' => 'El usuario es obligatorio',
            'username.unique' => 'Este usuario ya existe',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'rol.required' => 'Debe seleccionar un rol',
        ]);
        
        // Crear usuario
        $nuevoUsuario = Usuario::create([
            'nombre' => $validated['nombre'],
            'email' => $validated['email'] ?? null,
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'rol' => $validated['rol'],
            'activo' => $request->has('activo'),
        ]);
        
        // Registrar actividad
        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_USUARIO,
            "Usuario creado: {$nuevoUsuario->nombre} ({$nuevoUsuario->rol})",
            ['usuario_id' => $nuevoUsuario->id, 'accion' => 'crear']
        );
        
        return redirect("/{$tenant->rut}/usuarios")
            ->with('success', 'Usuario creado exitosamente');
    }

    /**
     * Mostrar formulario de edición
     * 
     * GET /{tenant}/usuarios/{id}/editar
     */
    public function edit(Request $request, $tenantRut, $id)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        $usuarioEditar = Usuario::findOrFail($id);
        
        return view('usuarios.editar', [
            'tenant' => $tenant,
            'usuario' => $usuario,
            'usuarioEditar' => $usuarioEditar,
        ]);
    }

    /**
     * Actualizar usuario
     * 
     * PUT /{tenant}/usuarios/{id}
     */
    public function update(Request $request, $tenantRut, $id)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        $usuarioEditar = Usuario::findOrFail($id);
        
        // Validar
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => "nullable|email|max:255|unique:usuarios,email,{$id}",
            'username' => "required|string|max:100|unique:usuarios,username,{$id}",
            'rol' => 'required|in:admin,supervisor,operario',
            'activo' => 'boolean',
        ]);
        
        // Actualizar
        $usuarioEditar->update([
            'nombre' => $validated['nombre'],
            'email' => $validated['email'] ?? null,
            'username' => $validated['username'],
            'rol' => $validated['rol'],
            'activo' => $request->has('activo'),
        ]);
        
        // Registrar actividad
        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_USUARIO,
            "Usuario actualizado: {$usuarioEditar->nombre}",
            ['usuario_id' => $usuarioEditar->id, 'accion' => 'actualizar']
        );
        
        return redirect("/{$tenant->rut}/usuarios")
            ->with('success', 'Usuario actualizado exitosamente');
    }

    /**
     * Cambiar contraseña
     * 
     * POST /{tenant}/usuarios/{id}/cambiar-password
     */
    public function cambiarPassword(Request $request, $tenantRut, $id)
    {
        $usuario = $request->attributes->get('usuario');
        $usuarioEditar = Usuario::findOrFail($id);
        
        // Validar
        $validated = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ]);
        
        // Actualizar contraseña
        $usuarioEditar->update([
            'password' => Hash::make($validated['password']),
        ]);
        
        // Registrar actividad
        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_USUARIO,
            "Contraseña cambiada para usuario: {$usuarioEditar->nombre}",
            ['usuario_id' => $usuarioEditar->id, 'accion' => 'cambiar_password']
        );
        
        return back()->with('success', 'Contraseña actualizada exitosamente');
    }

    /**
     * Activar/Desactivar usuario
     * 
     * POST /{tenant}/usuarios/{id}/toggle
     */
    public function toggle(Request $request, $tenantRut, $id)
    {
        $usuario = $request->attributes->get('usuario');
        $usuarioEditar = Usuario::findOrFail($id);
        
        // No permitir desactivar el propio usuario
        if ($usuarioEditar->id === $usuario->id) {
            return back()->with('error', 'No puedes desactivar tu propio usuario');
        }
        
        $usuarioEditar->activo = !$usuarioEditar->activo;
        $usuarioEditar->save();
        
        $estado = $usuarioEditar->activo ? 'activado' : 'desactivado';
        
        // Registrar actividad
        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_USUARIO,
            "Usuario {$estado}: {$usuarioEditar->nombre}",
            ['usuario_id' => $usuarioEditar->id, 'accion' => 'toggle']
        );
        
        return back()->with('success', "Usuario {$estado} exitosamente");
    }
}
