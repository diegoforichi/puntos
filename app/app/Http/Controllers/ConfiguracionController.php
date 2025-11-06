<?php

namespace App\Http\Controllers;

use App\Mail\TestEmailSmtp;
use App\Models\Actividad;
use App\Models\Configuracion;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Controlador de Configuración del Tenant
 *
 * Permite configurar:
 * - Puntos por pesos
 * - Días de vencimiento
 * - Datos de contacto
 * - Eventos de WhatsApp
 */
class ConfiguracionController extends Controller
{
    /**
     * Mostrar página de configuración
     *
     * GET /{tenant}/configuracion
     */
    public function index(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');

        // Obtener configuraciones actuales
        $puntosConfig = ['valor' => Configuracion::get('puntos_por_pesos', 100)];
        $vencimientoConfig = ['valor' => Configuracion::get('dias_vencimiento', 180)];
        $contactoConfig = Configuracion::getContacto();
        $eventosWhatsApp = Configuracion::get('eventos_whatsapp', [
            'puntos_canjeados' => true,
            'puntos_por_vencer' => true,
            'promociones_activas' => false,
            'bienvenida_nuevos' => false,
        ]);
        $customWhatsApp = Configuracion::getCustomWhatsAppConfig();
        $customEmail = Configuracion::getCustomEmailConfig();
        $configAcumulacion = Configuracion::getAcumulacion();
        $configMoneda = [
            'moneda_base' => Configuracion::getMonedaBase(),
            'tasa_usd' => Configuracion::getTasaUsd(),
            'moneda_desconocida' => Configuracion::getMonedaDesconocida(),
        ];
        $temaConfig = Configuracion::getTemaColores();

        return view('configuracion.index', [
            'tenant' => $tenant,
            'usuario' => $usuario,
            'puntosConfig' => $puntosConfig,
            'vencimientoConfig' => $vencimientoConfig,
            'contactoConfig' => $contactoConfig,
            'eventosWhatsApp' => $eventosWhatsApp,
            'customWhatsApp' => $customWhatsApp,
            'customEmail' => $customEmail,
            'configAcumulacion' => $configAcumulacion,
            'configMoneda' => $configMoneda,
            'temaConfig' => $temaConfig,
        ]);
    }

    /**
     * Actualizar configuración de puntos
     *
     * POST /{tenant}/configuracion/puntos
     */
    public function actualizarPuntos(Request $request)
    {
        $usuario = $request->attributes->get('usuario');
        session()->flash('tab', 'puntos');

        $validated = $request->validate([
            'puntos_por_pesos' => 'required|numeric|min:1',
        ], [
            'puntos_por_pesos.required' => 'El valor es obligatorio',
            'puntos_por_pesos.numeric' => 'Debe ser un número',
            'puntos_por_pesos.min' => 'Debe ser mayor a 0',
        ]);

        Configuracion::set('puntos_por_pesos', [
            'valor' => $validated['puntos_por_pesos'],
        ]);

        // Registrar actividad
        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_CONFIG,
            "Configuración actualizada: Puntos por pesos = {$validated['puntos_por_pesos']}",
            ['puntos_por_pesos' => $validated['puntos_por_pesos']]
        );

        return back()->with([
            'success' => 'Configuración de puntos actualizada correctamente',
            'tab' => 'puntos',
        ]);
    }

    /**
     * Actualizar configuración de vencimiento
     *
     * POST /{tenant}/configuracion/vencimiento
     */
    public function actualizarVencimiento(Request $request)
    {
        $usuario = $request->attributes->get('usuario');
        session()->flash('tab', 'puntos');

        $validated = $request->validate([
            'dias_vencimiento' => 'required|integer|min:1',
        ], [
            'dias_vencimiento.required' => 'El valor es obligatorio',
            'dias_vencimiento.integer' => 'Debe ser un número entero',
            'dias_vencimiento.min' => 'Debe ser al menos 1 día',
        ]);

        Configuracion::set('dias_vencimiento', [
            'valor' => $validated['dias_vencimiento'],
        ]);

        // Registrar actividad
        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_CONFIG,
            "Configuración actualizada: Días de vencimiento = {$validated['dias_vencimiento']}",
            ['dias_vencimiento' => $validated['dias_vencimiento']]
        );

        return back()->with([
            'success' => 'Configuración de vencimiento actualizada correctamente',
            'tab' => 'puntos',
        ]);
    }

    /**
     * Actualizar datos de contacto
     *
     * POST /{tenant}/configuracion/contacto
     */
    public function actualizarContacto(Request $request)
    {
        $usuario = $request->attributes->get('usuario');
        session()->flash('tab', 'contacto');

        $validated = $request->validate([
            'nombre_comercial' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
        ], [
            'nombre_comercial.required' => 'El nombre comercial es obligatorio',
            'email.email' => 'El email debe ser válido',
        ]);

        // Sanitizar datos (convertir null a string vacío)
        Configuracion::set('contacto', [
            'nombre_comercial' => trim($validated['nombre_comercial']),
            'telefono' => $validated['telefono'] ? trim($validated['telefono']) : '',
            'direccion' => $validated['direccion'] ? trim($validated['direccion']) : '',
            'email' => $validated['email'] ? trim($validated['email']) : '',
        ]);

        // Registrar actividad
        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_CONFIG,
            'Datos de contacto actualizados',
            ['contacto' => $validated]
        );

        return back()->with([
            'success' => 'Datos de contacto actualizados correctamente',
            'tab' => 'contacto',
        ]);
    }

    /**
     * Actualizar eventos de WhatsApp
     *
     * POST /{tenant}/configuracion/whatsapp
     */
    public function actualizarWhatsApp(Request $request)
    {
        $usuario = $request->attributes->get('usuario');
        session()->flash('tab', 'whatsapp');

        $eventos = [
            'puntos_canjeados' => $request->has('puntos_canjeados'),
            'puntos_por_vencer' => $request->has('puntos_por_vencer'),
            'promociones_activas' => $request->has('promociones_activas'),
            'bienvenida_nuevos' => $request->has('bienvenida_nuevos'),
        ];

        Configuracion::set('eventos_whatsapp', $eventos);

        // Registrar actividad
        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_CONFIG,
            'Eventos de WhatsApp actualizados',
            ['eventos' => $eventos]
        );

        return back()->with([
            'success' => 'Configuración de WhatsApp actualizada correctamente',
            'tab' => 'whatsapp',
        ]);
    }

    /**
     * Actualizar reglas de acumulación de puntos
     *
     * POST /{tenant}/configuracion/acumulacion
     */
    public function actualizarAcumulacion(Request $request)
    {
        $usuario = $request->attributes->get('usuario');
        session()->flash('tab', 'puntos');

        $excluirEfacturas = $request->has('acumulacion_excluir_efacturas');

        Configuracion::setAcumulacion($excluirEfacturas);

        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_CONFIG,
            'Actualizó reglas de acumulación de puntos',
            [
                'acumulacion_excluir_efacturas' => $excluirEfacturas,
            ]
        );

        return back()->with([
            'success' => 'Reglas de acumulación actualizadas correctamente',
            'tab' => 'puntos',
        ]);
    }

    public function actualizarMoneda(Request $request)
    {
        $usuario = $request->attributes->get('usuario');
        session()->flash('tab', 'puntos');

        $validated = $request->validate([
            'moneda_base' => 'required|string|max:10',
            'tasa_usd' => 'required|numeric|min:0',
            'moneda_desconocida' => 'required|in:omitir,sin_convertir',
        ]);

        Configuracion::setMonedaConfig(
            $validated['moneda_base'],
            (float) $validated['tasa_usd'],
            $validated['moneda_desconocida']
        );

        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_CONFIG,
            'Actualizó la configuración de moneda para puntos',
            $validated
        );

        return back()->with([
            'success' => 'Configuración de moneda actualizada correctamente',
            'tab' => 'puntos',
        ]);
    }

    public function actualizarTema(Request $request)
    {
        $usuario = $request->attributes->get('usuario');
        session()->flash('tab', 'tema');

        $validated = $request->validate([
            'color_primario' => 'required|string|max:20',
            'color_primario_claro' => 'required|string|max:20',
            'color_secundario' => 'required|string|max:20',
        ]);

        Configuracion::setTemaColores([
            'primario' => $validated['color_primario'],
            'primario_claro' => $validated['color_primario_claro'],
            'secundario' => $validated['color_secundario'],
        ]);

        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_CONFIG,
            'Actualizó la paleta de colores del tenant',
            $validated
        );

        return back()->with([
            'success' => 'Colores actualizados correctamente.',
            'tab' => 'tema',
        ]);
    }

    public function guardarWhatsAppPersonalizado(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');

        session()->flash('tab', 'integraciones');

        if (! $tenant->allow_custom_whatsapp) {
            return back()->with('error', 'El SuperAdmin no habilitó WhatsApp personalizado para este comercio.');
        }

        $validated = $request->validate([
            'url' => ['required', 'url', 'max:255'],
            'token' => ['required', 'string', 'max:255'],
            'activo' => ['nullable', 'boolean'],
        ], [
            'url.required' => 'La URL del servicio es obligatoria.',
            'url.url' => 'La URL del servicio no tiene un formato válido.',
            'token.required' => 'El token es obligatorio.',
        ]);

        Configuracion::setCustomWhatsAppConfig([
            'url' => trim($validated['url']),
            'token' => trim($validated['token']),
            'usar_canal' => $request->boolean('usar_canal'),
            'activo' => $request->boolean('activo'),
        ]);

        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_CONFIG,
            'Actualizó la configuración de WhatsApp personalizado',
            [
                'url' => $validated['url'],
                'activo' => (bool) ($validated['activo'] ?? false),
            ]
        );

        return back()->with('success', 'WhatsApp personalizado guardado correctamente.');
    }

    public function guardarEmailPersonalizado(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');

        session()->flash('tab', 'integraciones');

        if (! $tenant->allow_custom_email) {
            return back()->with('error', 'El SuperAdmin no habilitó Email personalizado para este comercio.');
        }

        $validated = $request->validate([
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1'],
            'encryption' => ['nullable', 'in:tls,ssl'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
        ]);

        Configuracion::setCustomEmailConfig([
            'host' => trim($validated['host']),
            'port' => (int) $validated['port'],
            'encryption' => $validated['encryption'] ?? null,
            'username' => trim($validated['username']),
            'password' => $validated['password'],
            'from_address' => trim($validated['from_address']),
            'from_name' => trim($validated['from_name']),
            'usar_canal' => $request->boolean('usar_canal'),
            'activo' => $request->boolean('activo'),
        ]);

        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_CONFIG,
            'Actualizó la configuración SMTP personalizada',
            [
                'host' => $validated['host'],
                'from_address' => $validated['from_address'],
                'encryption' => $validated['encryption'] ?? null,
            ]
        );

        return back()->with('success', 'Email personalizado guardado correctamente.');
    }

    public function probarWhatsAppPersonalizado(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');

        session()->flash('tab', 'integraciones');

        if (! $tenant->allow_custom_whatsapp) {
            return back()->with('error', 'El SuperAdmin no habilitó WhatsApp personalizado para este comercio.');
        }

        $datos = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
            'mensaje' => ['nullable', 'string', 'max:500'],
        ]);

        $config = Configuracion::getCustomWhatsAppConfig();

        if (! ($config['usar_canal'] ?? true)) {
            return back()->with('error', 'El canal de WhatsApp está desactivado. Actívalo y guarda los cambios antes de probar.');
        }

        if (! ($config['activo'] ?? false)) {
            return back()->with('error', 'Debes activar el uso de credenciales personalizadas antes de realizar la prueba.');
        }

        $mensaje = $datos['mensaje'] ?? 'Mensaje de prueba desde el panel de integraciones.';

        $resultado = WhatsAppService::enviar($config, $datos['telefono'], $mensaje, $tenant);

        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_CONFIG,
            'Ejecutó prueba de WhatsApp personalizado',
            [
                'telefono' => $datos['telefono'],
                'exitoso' => $resultado['success'] ?? false,
            ]
        );

        if ($resultado['success'] ?? false) {
            return back()->with('success', 'Mensaje de prueba enviado correctamente. Revisa el registro de WhatsApp para más detalles.');
        }

        return back()->with('error', 'No se pudo enviar el mensaje: '.($resultado['message'] ?? 'Error desconocido.'));
    }

    public function probarEmailPersonalizado(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');

        session()->flash('tab', 'integraciones');

        if (! $tenant->allow_custom_email) {
            return back()->with('error', 'El SuperAdmin no habilitó Email personalizado para este comercio.');
        }

        $datos = $request->validate([
            'email_prueba' => ['required', 'email'],
        ]);

        $config = Configuracion::getCustomEmailConfig();

        if (! ($config['usar_canal'] ?? true)) {
            return back()->with('error', 'El canal de Email está desactivado. Actívalo y guarda los cambios antes de probar.');
        }

        if (! ($config['activo'] ?? false)) {
            return back()->with('error', 'Debes activar el uso de SMTP personalizado antes de realizar la prueba.');
        }

        $requeridos = ['host', 'port', 'username', 'password', 'from_address', 'from_name'];
        foreach ($requeridos as $campo) {
            if (empty($config[$campo])) {
                return back()->with('error', 'Completa y guarda la configuración SMTP antes de realizar la prueba.');
            }
        }

        config([
            'mail.mailers.smtp.host' => $config['host'],
            'mail.mailers.smtp.port' => $config['port'],
            'mail.mailers.smtp.encryption' => $config['encryption'] ?? null,
            'mail.mailers.smtp.username' => $config['username'],
            'mail.mailers.smtp.password' => $config['password'],
            'mail.from.address' => $config['from_address'],
            'mail.from.name' => $config['from_name'],
        ]);

        try {
            Mail::mailer('smtp')
                ->to($datos['email_prueba'])
                ->send(new TestEmailSmtp);

            Actividad::registrar(
                $usuario->id,
                Actividad::ACCION_CONFIG,
                'Ejecutó prueba de Email personalizado',
                [
                    'email' => $datos['email_prueba'],
                ]
            );

            return back()->with('success', 'Correo de prueba enviado correctamente. Revisa la casilla ingresada.');
        } catch (\Throwable $e) {
            Actividad::registrar(
                $usuario->id,
                Actividad::ACCION_CONFIG,
                'Error en prueba de Email personalizado',
                [
                    'email' => $datos['email_prueba'],
                    'error' => $e->getMessage(),
                ]
            );

            return back()->with('error', 'No se pudo enviar el correo: '.$e->getMessage());
        }
    }

    /**
     * Compactar base de datos eliminando facturas antiguas
     *
     * POST /{tenant}/configuracion/compactar
     */
    public function compactarBaseDatos(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        session()->flash('tab', 'mantenimiento');

        try {
            $mesesAntiguedad = 12;
            $fechaLimite = now()->subMonths($mesesAntiguedad);

            // Contar facturas antes
            $totalAntes = \DB::connection('tenant')->table('facturas')->count();
            $antiguasCount = \DB::connection('tenant')->table('facturas')
                ->where('created_at', '<', $fechaLimite)
                ->count();

            if ($antiguasCount === 0) {
                return back()->with([
                    'info' => 'No hay facturas antiguas para eliminar.',
                    'tab' => 'mantenimiento',
                ]);
            }

            // Eliminar facturas antiguas
            \DB::connection('tenant')->table('facturas')
                ->where('created_at', '<', $fechaLimite)
                ->delete();

            // Eliminar registros huérfanos en webhook_inbox (opcional)
            \DB::connection('tenant')->table('webhook_inbox')
                ->where('created_at', '<', $fechaLimite)
                ->delete();

            // Ejecutar VACUUM para compactar el archivo SQLite
            \DB::connection('tenant')->statement('VACUUM');

            // Contar facturas después
            $totalDespues = \DB::connection('tenant')->table('facturas')->count();

            // Registrar actividad
            Actividad::registrar(
                $usuario->id,
                Actividad::ACCION_CONFIG,
                'Compactó la base de datos',
                [
                    'facturas_antes' => $totalAntes,
                    'facturas_eliminadas' => $antiguasCount,
                    'facturas_despues' => $totalDespues,
                    'meses_antiguedad' => $mesesAntiguedad,
                ]
            );

            return back()->with([
                'success' => "Base de datos compactada exitosamente. Se eliminaron {$antiguasCount} facturas antiguas.",
                'tab' => 'mantenimiento',
            ]);

        } catch (\Exception $e) {
            \Log::error('Error compactando base de datos', [
                'tenant' => $tenant->rut,
                'error' => $e->getMessage(),
            ]);

            return back()->with([
                'error' => 'Error al compactar la base de datos: '.$e->getMessage(),
                'tab' => 'mantenimiento',
            ]);
        }
    }
}
