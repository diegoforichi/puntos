<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;

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
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar si hay usuario en sesión
        $userId = session('usuario_id');

        $tenantRut = $request->route('tenant');
        $sessionData = session("tenant_sessions.{$tenantRut}");

        if (! $userId || ! $sessionData) {
            session()->forget([
                'tenant_rut',
                'usuario_id',
                'usuario_nombre',
                'usuario_email',
                'usuario_rol',
            ]);

            return redirect("/{$tenantRut}/login")
                ->with('error', 'Debe iniciar sesión para continuar');
        }

        // Verificar que el usuario existe y está activo en la base del tenant
        // Usar el modelo Eloquent para tener acceso a accessors y métodos
        $usuario = Usuario::where('id', $userId)
            ->where('activo', 1)
            ->first();

        if (! $usuario) {
            session()->forget([
                'tenant_rut',
                'usuario_id',
                'usuario_nombre',
                'usuario_email',
                'usuario_rol',
            ]);
            session()->forget("tenant_sessions.{$tenantRut}");

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
        $sessionPayload = [
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->nombre,
            'usuario_email' => $usuario->email,
            'usuario_rol' => $usuario->rol,
        ];

        session()->put("tenant_sessions.{$tenantRut}", $sessionPayload);
        session([
            'tenant_rut' => $tenantRut,
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->nombre,
            'usuario_email' => $usuario->email,
            'usuario_rol' => $usuario->rol,
        ]);

        return $next($request);
    }
}
