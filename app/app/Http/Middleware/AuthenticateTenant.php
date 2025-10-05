<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;

/**
 * Middleware para verificar autenticación del usuario en el tenant
 * 
 * Verifica que:
 * 1. El usuario tenga una sesión activa
 * 2. El usuario existe en la tabla 'usuarios' del tenant
 * 3. El usuario está activo
 * 
 * Si no está autenticado, redirige al login del tenant
 */
class AuthenticateTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar si hay usuario en sesión
        $userId = session('usuario_id');

        if (!$userId) {
            $tenantRut = $request->route('tenant');
            return redirect("/{$tenantRut}/login")
                ->with('error', 'Debe iniciar sesión para continuar');
        }

        // Verificar que el usuario existe y está activo en la base del tenant
        // Usar el modelo Eloquent para tener acceso a accessors y métodos
        $usuario = Usuario::where('id', $userId)
            ->where('activo', 1)
            ->first();

        if (!$usuario) {
            session()->forget(['usuario_id', 'usuario_nombre', 'usuario_email', 'usuario_rol']);

            $tenantRut = $request->route('tenant');
            return redirect("/{$tenantRut}/login")
                ->with('error', 'Usuario no autorizado o inactivo');
        }

        // Actualizar último acceso
        $usuario->ultimo_acceso = now();
        $usuario->save();

        // Guardar usuario en la request para uso en controladores
        $request->attributes->add(['usuario' => $usuario]);

        // Compartir usuario con todas las vistas
        view()->share('usuario', $usuario);

        // Guardar campos básicos en sesión si no existen
        session([
            'usuario_nombre' => $usuario->nombre,
            'usuario_email' => $usuario->email,
            'usuario_rol' => $usuario->rol,
        ]);

        return $next($request);
    }
}
