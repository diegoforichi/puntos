<?php

namespace App\Http\Controllers;

use App\Http\Requests\Campanas\EnviarPruebaCampanaRequest;
use App\Jobs\EnviarCampanaJob;
use App\Mail\CampanaMail;
use App\Models\Actividad;
use App\Models\Campana;
use App\Models\CampanaEnvio;
use App\Models\Cliente;
use App\Services\NotificationConfigResolver;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class CampanaController extends Controller
{
    public function index(Request $request)
    {
        $tenant = $request->attributes->get('tenant');

        $campanas = Campana::query()
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('campanas.index', compact('campanas', 'tenant'));
    }

    public function create(Request $request)
    {
        $tenant = $request->attributes->get('tenant');

        return view('campanas.create', compact('tenant'));
    }

    public function store(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'canal' => ['required', Rule::in(['whatsapp', 'email', 'ambos'])],
            'segmento' => ['required', Rule::in(['todos', 'activos', 'inactivos'])],
            'mensaje_whatsapp' => ['nullable', 'string', 'max:200'],
            'titulo_email' => ['nullable', 'string', 'max:255'],
            'subtitulo_email' => ['nullable', 'string', 'max:255'],
            'imagen_email' => ['nullable', 'url', 'max:255'],
            'texto_email' => ['nullable', 'string', 'max:10000'],
            'asunto_email' => ['nullable', 'string', 'max:255'],
            'programar' => ['nullable', 'boolean'],
            'fecha_programada' => ['nullable', 'date'],
            'hora_programada' => ['nullable', 'date_format:H:i'],
        ]);

        if (in_array($validated['canal'], ['whatsapp', 'ambos']) && empty($validated['mensaje_whatsapp'])) {
            return back()->withErrors(['mensaje_whatsapp' => 'El mensaje de WhatsApp es obligatorio para este canal.'])->withInput();
        }

        if (in_array($validated['canal'], ['email', 'ambos']) && empty($validated['texto_email'])) {
            return back()->withErrors(['texto_email' => 'El contenido principal del email es obligatorio para este canal.'])->withInput();
        }

        if (! empty($validated['programar']) && (empty($validated['fecha_programada']) || empty($validated['hora_programada']))) {
            return back()->withErrors(['programar' => 'Debes indicar fecha y hora para programar la campaña.'])->withInput();
        }

        $destinatarios = collect();
        $enviosCreados = collect();
        $campanaCreada = null;

        $enviarInmediato = $request->boolean('enviar_inmediato');

        DB::connection('tenant')->transaction(function () use ($validated, $usuario, $tenant, $enviarInmediato, &$destinatarios, &$enviosCreados, &$campanaCreada) {
            $fechaProgramada = null;

            if (! empty($validated['programar'])) {
                $fechaProgramada = Carbon::createFromFormat(
                    'Y-m-d H:i',
                    sprintf('%s %s', $validated['fecha_programada'], $validated['hora_programada']),
                    config('app.timezone')
                )->seconds(0);
            }

            $campana = Campana::create([
                'tenant_id' => $tenant->id,
                'canal' => $validated['canal'],
                'tipo_envio' => $validated['segmento'],
                'titulo' => $validated['titulo_email'] ?? $validated['nombre'],
                'subtitulo' => $validated['subtitulo_email'] ?? null,
                'imagen_url' => $validated['imagen_email'] ?? null,
                'asunto_email' => $validated['asunto_email'] ?? null,
                'cuerpo_texto' => $validated['texto_email'] ?? $validated['mensaje_whatsapp'],
                'mensaje_whatsapp' => $validated['mensaje_whatsapp'] ?? null,
                'fecha_programada' => $fechaProgramada?->toDateTimeString(),
                'estado' => $fechaProgramada ? 'pendiente' : ($enviarInmediato ? 'en_cola' : 'borrador'),
                // Guardar como JSON explícito por compatibilidad con SQLite
                'totales' => json_encode([
                    'clientes' => 0,
                    'whatsapp' => 0,
                    'email' => 0,
                    'exitosos' => 0,
                    'fallidos' => 0,
                ], JSON_UNESCAPED_UNICODE),
            ]);

            $destinatarios = $this->obtenerDestinatarios($campana, $validated['segmento']);
            $campanaCreada = $campana;

            $canales = $campana->canales();
            foreach ($destinatarios as $cliente) {
                foreach ($canales as $canal) {
                    if ($canal === 'whatsapp') {
                        $telefono = method_exists($cliente, 'getTelefonoWhatsappAttribute')
                            ? $cliente->telefono_whatsapp
                            : $cliente->telefono;

                        if (empty($telefono)) {
                            continue;
                        }
                    }

                    if ($canal === 'email' && empty($cliente->email)) {
                        continue;
                    }

                    $enviosCreados->push([
                        'campana_id' => $campana->id,
                        'cliente_id' => $cliente->id,
                        'canal' => $canal,
                        'estado' => 'pendiente',
                        'intentos' => 0,
                        'error_mensaje' => null,
                        'created_at' => now()->toDateTimeString(),
                        'updated_at' => now()->toDateTimeString(),
                    ]);
                }
            }

            Actividad::registrar(
                $usuario->id,
                Actividad::ACCION_CAMPANIA,
                'Creó campaña masiva',
                [
                    'campana_id' => $campana->id,
                    'canal' => $campana->canal,
                    'tipo_envio' => $campana->tipo_envio,
                    'clientes_considerados' => $destinatarios->count(),
                    'envios_creados' => $enviosCreados->count(),
                ]
            );
        });

        $campana = $campanaCreada;

        // Insertar envíos en batch fuera de la transacción para evitar bloqueos
        if ($enviosCreados->isNotEmpty()) {
            CampanaEnvio::insert($enviosCreados->all());
        }

        // Actualizar totales
        $campana->update([
            // Guardar como JSON explícito por compatibilidad con SQLite
            'totales' => json_encode([
                'clientes' => $destinatarios->count(),
                'whatsapp' => $enviosCreados->where('canal', 'whatsapp')->count(),
                'email' => $enviosCreados->where('canal', 'email')->count(),
                'exitosos' => 0,
                'fallidos' => 0,
            ], JSON_UNESCAPED_UNICODE),
        ]);

        if (! $campana->esProgramada() && $enviarInmediato) {
            EnviarCampanaJob::dispatch($campana->id)->onQueue('campanas');
        }

        return redirect()->route('tenant.campanas.index', $tenant->rut)
            ->with('success', 'Campaña creada correctamente.');
    }

    public function show(Request $request, string $tenantRut, int $id)
    {
        $campana = Campana::findOrFail($id);
        $tenant = $request->attributes->get('tenant');

        $campana->load(['envios' => fn ($query) => $query->latest()->limit(50)]);

        $resumenEnvios = CampanaEnvio::query()
            ->where('campana_id', $campana->id)
            ->selectRaw("COUNT(*) as total,
                SUM(CASE WHEN estado = 'enviado' THEN 1 ELSE 0 END) as enviados,
                SUM(CASE WHEN estado = 'fallido' THEN 1 ELSE 0 END) as fallidos")
            ->first();

        $destinatariosUnicos = CampanaEnvio::query()
            ->where('campana_id', $campana->id)
            ->distinct()
            ->count('cliente_id');

        $conteoEnvios = [
            'programados' => (int) ($resumenEnvios?->total ?? 0),
            'enviados' => (int) ($resumenEnvios?->enviados ?? 0),
            'fallidos' => (int) ($resumenEnvios?->fallidos ?? 0),
            'destinatarios' => (int) $destinatariosUnicos,
        ];

        return view('campanas.show', compact('campana', 'tenant', 'conteoEnvios'));
    }

    public function schedule(Request $request, string $tenantRut, int $id)
    {
        $campana = Campana::findOrFail($id);
        $validated = $request->validate([
            'fecha_programada' => ['required', 'date'],
            'hora_programada' => ['required', 'date_format:H:i'],
        ]);

        $programacion = Carbon::createFromFormat(
            'Y-m-d H:i',
            sprintf('%s %s', $validated['fecha_programada'], $validated['hora_programada']),
            config('app.timezone')
        )->seconds(0);

        $campana->update([
            'fecha_programada' => $programacion->toDateTimeString(),
            'estado' => 'pendiente',
        ]);

        return back()->with('success', 'Campaña reprogramada correctamente.');
    }

    public function sendNow(Request $request, string $tenantRut, int $id)
    {
        $campana = Campana::findOrFail($id);
        if (! $campana->puedeEnviarse()) {
            return back()->with('error', 'La campaña no puede enviarse en su estado actual.');
        }

        $enviosPendientes = $campana->envios()->where('estado', 'pendiente')->count();

        if ($enviosPendientes === 0) {
            $resultado = $this->prepararEnviosParaCampana($campana);

            if ($resultado['total_envios'] === 0) {
                return back()->with('error', 'No hay destinatarios válidos para enviar esta campaña.');
            }

            $campana->refresh();
        }

        $campana->update([
            'fecha_programada' => null,
            'estado' => 'en_cola',
        ]);

        EnviarCampanaJob::dispatch($campana->id)->onQueue('campanas');

        return back()->with('success', 'Campaña marcada para envío inmediato.');
    }

    public function sendTest(
        EnviarPruebaCampanaRequest $request,
        string $tenantRut,
        int $id,
        NotificationConfigResolver $configResolver,
        WhatsAppService $whatsAppService
    ) {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        $campana = Campana::findOrFail($id);

        $validated = $request->validated();

        $telefono = trim((string) ($validated['telefono_prueba'] ?? '')) ?: null;
        $email = trim((string) ($validated['email_prueba'] ?? '')) ?: null;

        if (! $telefono && ! $email) {
            return back()
                ->withErrors(['telefono_prueba' => 'Ingresa al menos un teléfono o un email para enviar la prueba.'])
                ->withInput()
                ->with('mostrar_modal_prueba', true);
        }

        $canales = $campana->canales();

        $clienteSimulado = new Cliente([
            'nombre' => 'Cliente Prueba',
            'telefono' => $telefono,
            'email' => $email,
            'documento' => 'TEST',
            'puntos_acumulados' => 0,
        ]);

        $mensajesExito = [];
        $errores = [];

        if (in_array('whatsapp', $canales, true) && $telefono) {
            try {
                $configWhatsApp = $configResolver->resolveWhatsAppConfig($tenant);

                if (! ($configWhatsApp['usar_canal'] ?? false)) {
                    throw new \RuntimeException('El canal de WhatsApp está deshabilitado.');
                }

                $mensaje = $campana->construirMensajeWhatsapp($clienteSimulado, $tenant);

                if (! $mensaje) {
                    throw new \RuntimeException('El mensaje de WhatsApp está vacío.');
                }

                $telefonoDestino = $clienteSimulado->telefono_whatsapp ?? $telefono;
                $respuesta = $whatsAppService->enviar($configWhatsApp, $telefonoDestino, $mensaje, $tenant);

                if (! ($respuesta['success'] ?? false)) {
                    throw new \RuntimeException($respuesta['message'] ?? 'No se pudo enviar la prueba de WhatsApp.');
                }

                $mensajesExito[] = "WhatsApp de prueba enviado a {$telefonoDestino}";
            } catch (\Throwable $e) {
                $errores[] = 'WhatsApp: '.$e->getMessage();
            }
        }

        if (in_array('email', $canales, true) && $email) {
            try {
                $configEmail = $configResolver->resolveEmailConfig($tenant);

                if (! ($configEmail['usar_canal'] ?? false)) {
                    throw new \RuntimeException('El canal de Email está deshabilitado.');
                }

                $contenido = $campana->obtenerContenidoEmail();

                if (empty($contenido['cuerpo'])) {
                    throw new \RuntimeException('El contenido del email está vacío.');
                }

                config([
                    'mail.mailers.smtp.host' => $configEmail['host'] ?? null,
                    'mail.mailers.smtp.port' => $configEmail['port'] ?? 587,
                    'mail.mailers.smtp.username' => $configEmail['username'] ?? null,
                    'mail.mailers.smtp.password' => $configEmail['password'] ?? null,
                    'mail.mailers.smtp.encryption' => $configEmail['encryption'] ?? null,
                    'mail.from.address' => $configEmail['from_address'] ?? config('mail.from.address'),
                    'mail.from.name' => $configEmail['from_name'] ?? config('mail.from.name'),
                ]);

                Mail::mailer('smtp')
                    ->to($email)
                    ->send(new CampanaMail($campana, $clienteSimulado, $contenido));

                $mensajesExito[] = "Email de prueba enviado a {$email}";
            } catch (\Throwable $e) {
                $errores[] = 'Email: '.$e->getMessage();
            }
        }

        if (! empty($errores)) {
            return back()
                ->withErrors(['prueba' => implode(' ', $errores)])
                ->withInput()
                ->with('mostrar_modal_prueba', true);
        }

        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_CAMPANIA,
            'Envió prueba de campaña',
            [
                'campana_id' => $campana->id,
                'telefono' => $telefono,
                'email' => $email,
            ]
        );

        $mensaje = $mensajesExito ? implode(' ', $mensajesExito) : 'Prueba realizada correctamente.';

        return back()->with('success', $mensaje);
    }

    private function obtenerDestinatarios(Campana $campana, string $segmento)
    {
        return $this->queryDestinatariosSegunSegmento($segmento)->get();
    }

    private function queryDestinatariosSegunSegmento(string $segmento)
    {
        return match ($segmento) {
            'activos' => Cliente::activos(60),
            'inactivos' => Cliente::inactivos(90),
            default => Cliente::query(),
        };
    }

    public function pause(Request $request, string $tenantRut, int $id)
    {
        $campana = Campana::findOrFail($id);
        $usuario = $request->attributes->get('usuario');

        if (! $campana->puedePausarse()) {
            return back()->with('error', 'Esta campaña no puede ser pausada en su estado actual.');
        }

        $campana->update(['estado' => 'pausada']);

        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_CAMPANIA,
            'Pausó campaña',
            ['campana_id' => $campana->id, 'titulo' => $campana->titulo]
        );

        return back()->with('success', 'Campaña pausada correctamente.');
    }

    public function resume(Request $request, string $tenantRut, int $id)
    {
        $campana = Campana::findOrFail($id);
        $usuario = $request->attributes->get('usuario');

        if (! $campana->puedeReanudarse()) {
            return back()->with('error', 'Esta campaña no puede ser reanudada.');
        }

        $campana->update(['estado' => 'pendiente']);

        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_CAMPANIA,
            'Reanudó campaña',
            ['campana_id' => $campana->id, 'titulo' => $campana->titulo]
        );

        return back()->with('success', 'Campaña reanudada correctamente.');
    }

    public function destroy(Request $request, string $tenantRut, int $id)
    {
        $campana = Campana::withTrashed()->findOrFail($id);
        $usuario = $request->attributes->get('usuario');

        if ($campana->puedeEliminarse()) {
            $titulo = $campana->titulo;
            $campana->delete();

            Actividad::registrar(
                $usuario->id,
                Actividad::ACCION_CAMPANIA,
                'Eliminó campaña',
                ['campana_id' => $id, 'titulo' => $titulo]
            );

            return redirect()->route('tenant.campanas.index', $tenantRut)
                ->with('success', 'Campaña eliminada correctamente.');
        }

        if ($campana->trashed()) {
            return redirect()->route('tenant.campanas.index', $tenantRut)
                ->with('success', 'La campaña ya estaba archivada.');
        }

        if ($campana->puedeArchivarse()) {
            $campana->delete();

            Actividad::registrar(
                $usuario->id,
                Actividad::ACCION_CAMPANIA,
                'Archivó campaña',
                ['campana_id' => $id, 'titulo' => $campana->titulo]
            );

            return redirect()->route('tenant.campanas.index', $tenantRut)
                ->with('success', 'Campaña archivada correctamente.');
        }

        return back()->with('error', 'Esta campaña no puede eliminarse ni archivarse en su estado actual.');
    }

    public function duplicate(Request $request, string $tenantRut, int $id)
    {
        $campana = Campana::findOrFail($id);
        $usuario = $request->attributes->get('usuario');

        $validated = $request->validate([
            'titulo_duplicado' => ['nullable', 'string', 'max:255'],
        ]);

        $nueva = $campana->duplicar();

        if (! empty($validated['titulo_duplicado'])) {
            $nueva->update(['titulo' => $validated['titulo_duplicado']]);
        }

        $resultados = $this->prepararEnviosParaCampana($nueva);

        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_CAMPANIA,
            'Duplicó campaña',
            [
                'campana_id' => $campana->id,
                'campana_duplicada_id' => $nueva->id,
            ]
        );

        $mensaje = 'Campaña duplicada como borrador.';
        if ($resultados['total_envios'] > 0) {
            $mensaje .= ' Se prepararon '.$resultados['total_envios'].' envíos.';
        } else {
            $mensaje .= ' No se encontraron destinatarios válidos todavía.';
        }

        return redirect()->route('tenant.campanas.show', [$tenantRut, $nueva->id])
            ->with('success', $mensaje);
    }

    private function prepararEnviosParaCampana(Campana $campana): array
    {
        $destinatarios = $this->obtenerDestinatarios($campana, $campana->tipo_envio);
        $canales = $campana->canales();

        $envios = collect();

        foreach ($destinatarios as $cliente) {
            foreach ($canales as $canal) {
                if ($canal === 'whatsapp') {
                    $telefono = method_exists($cliente, 'getTelefonoWhatsappAttribute')
                        ? $cliente->telefono_whatsapp
                        : $cliente->telefono;

                    if (empty($telefono)) {
                        continue;
                    }
                }

                if ($canal === 'email' && empty($cliente->email)) {
                    continue;
                }

                $envios->push([
                    'campana_id' => $campana->id,
                    'cliente_id' => $cliente->id,
                    'canal' => $canal,
                    'estado' => 'pendiente',
                    'intentos' => 0,
                    'error_mensaje' => null,
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ]);
            }
        }

        if ($envios->isNotEmpty()) {
            CampanaEnvio::insert($envios->all());
        }

        $whatsapp = $envios->where('canal', 'whatsapp')->count();
        $email = $envios->where('canal', 'email')->count();

        $campana->update([
            'totales' => json_encode([
                'clientes' => $destinatarios->count(),
                'whatsapp' => $whatsapp,
                'email' => $email,
                'exitosos' => 0,
                'fallidos' => 0,
            ], JSON_UNESCAPED_UNICODE),
        ]);

        return [
            'destinatarios' => $destinatarios->count(),
            'total_envios' => $envios->count(),
            'whatsapp' => $whatsapp,
            'email' => $email,
        ];
    }
}
