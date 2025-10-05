<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('superadmin');
        /** @var User|null $user */
        $user = $guard->user();

        if (!$user || !$user->isSuperAdmin() || $user->status !== User::STATUS_ACTIVE) {
            $guard->logout();

            return redirect()->route('superadmin.login')->with('error', 'Debe iniciar sesión como SuperAdmin.');
        }

        View::share('superadmin', $user);
        $request->attributes->set('superadmin', $user);

        return $next($request);
    }
}
