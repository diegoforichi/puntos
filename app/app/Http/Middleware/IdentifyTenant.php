<?php

namespace App\Http\Middleware;

use App\Models\Configuracion;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Middleware para identificar el tenant desde la URL
 *
 * Captura el parámetro {tenant} de la ruta y:
 * 1. Valida que el tenant existe y está activo
 * 2. Configura la conexión a su base SQLite
 * 3. Guarda el tenant en la request para uso posterior
 *
 * Rutas esperadas: /{tenant}/login, /{tenant}/dashboard, etc.
 */
class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Obtener RUT del tenant desde la URL
        $tenantRut = $request->route('tenant');

        $tenantSession = $tenantRut ? session("tenant_sessions.{$tenantRut}") : null;

        if (! $tenantRut) {
            if ($request->is('*/api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tenant no especificado',
                ], 400);
            }

            return redirect('/')->with('error', 'Tenant no especificado');
        }

        // Buscar tenant en MySQL (conexión por defecto)
        DB::setDefaultConnection('mysql');

        $tenant = Tenant::where('rut', $tenantRut)
            ->where('estado', 'activo')
            ->first();

        if (! $tenant) {
            if ($request->is('*/api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Comercio no encontrado o inactivo',
                ], 404);
            }

            return redirect('/')
                ->with('error', 'Comercio no encontrado o inactivo');
        }

        // Verificar que existe la base SQLite
        $sqlitePath = $tenant->getSqlitePath();
        if (! file_exists($sqlitePath)) {
            if ($request->is('*/api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Base de datos del comercio no encontrada',
                ], 500);
            }

            return redirect('/')
                ->with('error', 'Base de datos del comercio no encontrada');
        }

        // Configurar conexión a SQLite del tenant
        Config::set('database.connections.tenant', [
            'driver' => 'sqlite',
            'database' => $sqlitePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        // Purgar conexión anterior si existe y establecer como default
        DB::purge('tenant');
        DB::setDefaultConnection('tenant');

        if ($tenantSession) {
            session([
                'tenant_rut' => $tenantRut,
                'usuario_id' => $tenantSession['usuario_id'],
                'usuario_nombre' => $tenantSession['usuario_nombre'] ?? null,
                'usuario_email' => $tenantSession['usuario_email'] ?? null,
                'usuario_rol' => $tenantSession['usuario_rol'] ?? null,
            ]);
        } else {
            session()->forget(['tenant_rut', 'usuario_id', 'usuario_nombre', 'usuario_email', 'usuario_rol']);
        }

        // Guardar tenant en la request para uso en controladores
        $request->attributes->add(['tenant' => $tenant]);

        // Cargar configuración de colores del tenant
        $temaColores = Configuracion::getTemaColores();
        $request->attributes->set('tenant_tema', $temaColores);

        // Compartir tenant y tema con todas las vistas
        view()->share('tenant', $tenant);
        view()->share('tenantTema', $temaColores);

        return $next($request);
    }
}
