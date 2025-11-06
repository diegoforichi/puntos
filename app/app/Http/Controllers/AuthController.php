<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Controlador de Autenticación
 *
 * Gestiona:
 * - Login de usuarios del tenant
 * - Logout
 * - Validación de credenciales
 */
class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     *
     * GET /{tenant}/login
     */
    public function showLogin(Request $request)
    {
        $tenant = $request->attributes->get('tenant');

        if (session("tenant_sessions.{$tenant->rut}.usuario_id")) {
            return redirect("/{$tenant->rut}/dashboard");
        }

        return view('auth.login', [
            'tenant' => $tenant,
        ]);
    }

    /**
     * Procesar login
     *
     * POST /{tenant}/login
     */
    public function login(Request $request)
    {
        $tenant = $request->attributes->get('tenant');

        // Validar datos del formulario
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'El usuario o email es obligatorio',
            'password.required' => 'La contraseña es obligatoria',
        ]);

        $loginField = $request->input('email');

        $query = DB::table('usuarios')->where('activo', 1);
        if (str_contains($loginField, '@')) {
            $query->where('email', $loginField);
        } else {
            $query->where('username', $loginField);
        }

        $usuario = $query->first();

        // Verificar que existe y la contraseña es correcta
        if (! $usuario || ! Hash::check($request->password, $usuario->password)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Credenciales incorrectas');
        }

        session()->put("tenant_sessions.{$tenant->rut}", [
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->nombre,
            'usuario_email' => $usuario->email,
            'usuario_rol' => $usuario->rol,
        ]);

        // Guardar en sesión (valores inmediatos para la request actual)
        session([
            'tenant_rut' => $tenant->rut,
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->nombre,
            'usuario_email' => $usuario->email,
            'usuario_rol' => $usuario->rol,
        ]);

        // Actualizar último acceso
        DB::table('usuarios')
            ->where('id', $usuario->id)
            ->update(['ultimo_acceso' => now()]);

        // Registrar actividad
        $this->registrarActividad($usuario->id, 'login', 'Usuario inició sesión');

        return redirect("/{$tenant->rut}/dashboard")
            ->with('success', "Bienvenido, {$usuario->nombre}");
    }

    /**
     * Cerrar sesión
     *
     * POST /{tenant}/logout
     */
    public function logout(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');

        if ($usuario) {
            // Registrar actividad
            $this->registrarActividad($usuario->id, 'logout', 'Usuario cerró sesión');
        }

        session()->forget("tenant_sessions.{$tenant->rut}");

        // Limpiar sesión actual
        session()->forget([
            'tenant_rut',
            'usuario_id',
            'usuario_nombre',
            'usuario_email',
            'usuario_rol',
        ]);

        return redirect("/{$tenant->rut}/login")
            ->with('success', 'Sesión cerrada correctamente');
    }

    /**
     * Registrar actividad en el log
     *
     * @param  int  $usuarioId
     * @param  string  $accion
     * @param  string  $descripcion
     * @return void
     */
    private function registrarActividad($usuarioId, $accion, $descripcion)
    {
        DB::table('actividades')->insert([
            'usuario_id' => $usuarioId,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'datos_json' => json_encode([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
