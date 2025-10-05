<?php

namespace App\Http\Middleware;

use App\Models\AdminLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminLogAction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $action = null): Response
    {
        $response = $next($request);

        /** @var \App\Models\User|null $superadmin */
        $superadmin = $request->attributes->get('superadmin');

        if ($superadmin) {
            AdminLog::create([
                'user_id' => $superadmin->id,
                'accion' => $action ?? $request->route()?->getName() ?? 'accion_desconocida',
                'descripcion' => $request->attributes->get('admin_log_descripcion'),
                'ip_address' => $request->ip(),
                'metadata' => [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                ],
            ]);
        }

        return $response;
    }
}
