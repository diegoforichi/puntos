<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateTenantApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->attributes->get('tenant');

        if (!$tenant || !$tenant->api_token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tenant sin token configurado',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $request->bearerToken();

        if (!$token || !hash_equals($tenant->api_token, $token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $tenant->forceFill(['api_token_last_used_at' => now()])->save();

        return $next($request);
    }
}

