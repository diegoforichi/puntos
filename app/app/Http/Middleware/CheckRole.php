<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware para verificar roles de usuario
 * 
 * Roles disponibles:
 * - superadmin: Acceso total al sistema (gestión de tenants)
 * - admin: Acceso completo al tenant
 * - supervisor: Puede canjear puntos y modificar configuración
 * - operario: Solo consulta, requiere autorización para canjear
 * 
 * Uso: Route::middleware(['auth.tenant', 'check.role:admin,supervisor'])
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $usuario = $request->attributes->get('usuario');

        if (!$usuario) {
            $tenantRut = $request->route('tenant');
            return redirect("/{$tenantRut}/login")
                ->with('error', 'Debe iniciar sesión');
        }

        // Verificar si el rol del usuario está en los roles permitidos
        if (!in_array($usuario->rol, $roles)) {
            return back()->with('error', 'No tiene permisos para realizar esta acción');
        }

        return $next($request);
    }
}
