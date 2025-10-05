<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Obtener RUT del tenant desde la URL
        $tenantRut = $request->route('tenant');

        if (!$tenantRut) {
            return redirect('/')->with('error', 'Tenant no especificado');
        }

        // Buscar tenant en MySQL (conexión por defecto)
        DB::setDefaultConnection('mysql');
        
        $tenant = Tenant::where('rut', $tenantRut)
            ->where('estado', 'activo')
            ->first();

        if (!$tenant) {
            return redirect('/')
                ->with('error', 'Comercio no encontrado o inactivo');
        }

        // Verificar que existe la base SQLite
        $sqlitePath = $tenant->getSqlitePath();
        if (!file_exists($sqlitePath)) {
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

        // Guardar tenant en la request para uso en controladores
        $request->attributes->add(['tenant' => $tenant]);
        
        // Compartir tenant con todas las vistas
        view()->share('tenant', $tenant);

        return $next($request);
    }
}
