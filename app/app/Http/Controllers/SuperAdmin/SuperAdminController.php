<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Tenant;
use App\Models\SystemConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Services\TenantBackupService;
use App\Http\Requests\SuperAdmin\ArchiveTenantRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $ultimoWebhook = Tenant::max('ultimo_webhook');

        $stats = [
            'tenants_total' => Tenant::count(),
            'tenants_activos' => Tenant::activos()->count(),
            'tenants_suspendidos' => Tenant::where('estado', 'suspendido')->count(),
            'facturas_totales' => Tenant::sum('facturas_recibidas'),
            'puntos_totales' => Tenant::sum('puntos_generados_total'),
            'ultimo_webhook' => $ultimoWebhook ? Carbon::parse($ultimoWebhook) : null,
        ];

        $topTenants = Tenant::orderByDesc('facturas_recibidas')->limit(5)->get();

        $recentLogs = AdminLog::latest()->limit(10)->with('user:id,name')->get();

        return view('superadmin.dashboard', compact('stats', 'topTenants', 'recentLogs'));
    }

    public function config()
    {
        $emailConfig = SystemConfig::getEmailConfig();
        $whatsappConfig = SystemConfig::getWhatsAppConfig();

        return view('superadmin.config', compact('emailConfig', 'whatsappConfig'));
    }

    public function saveEmailConfig(Request $request)
    {
        $data = $request->validate([
            'smtp_host' => 'required|string|max:255',
            'smtp_port' => 'required|integer',
            'smtp_user' => 'required|string|max:255',
            'smtp_pass' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|in:tls,ssl',
            'from_address' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
        ]);

        $data['smtp_encryption'] = $data['smtp_encryption'] ?? null;

        if (empty($data['smtp_pass'])) {
            unset($data['smtp_pass']);
            $current = SystemConfig::getEmailConfig();
            $data['smtp_pass'] = $current['smtp_pass'] ?? '';
        }

        SystemConfig::set('email', $data, 'Configuración del servicio Email');

        $request->attributes->set('admin_log_descripcion', 'Actualizó la configuración de Email SMTP');

        return back()->with('success', 'Configuración de Email guardada correctamente.');
    }

    public function saveWhatsAppConfig(Request $request)
    {
        $data = $request->validate([
            'url' => 'required|url',
            'token' => 'nullable|string|max:255',
            'codigo_pais' => 'required|string|max:10',
        ]);

        $current = SystemConfig::getWhatsAppConfig();

        $data['activo'] = $request->has('activo');

        if (empty($data['token'])) {
            $data['token'] = $current['token'] ?? '';
        }

        SystemConfig::set('whatsapp', $data, 'Configuración del servicio WhatsApp');
        $request->attributes->set('admin_log_descripcion', 'Actualizó la configuración de WhatsApp');

        return back()->with('success', 'Configuración de WhatsApp guardada correctamente.');
    }

    public function tenants()
    {
        $tenants = Tenant::orderBy('created_at', 'desc')->paginate(10);

        return view('superadmin.tenants.index', compact('tenants'));
    }

    public function showTenant(Tenant $tenant)
    {
        $sqlitePath = $tenant->getSqlitePath();
        $sizeMB = File::exists($sqlitePath) ? round(File::size($sqlitePath) / 1024 / 1024, 2) : 0;

        $backupInfo = TenantBackupService::lastBackupMetadata($tenant);

        $webhooks = DB::table('webhook_inbox_global')
            ->where('tenant_rut', $tenant->rut)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $demoCredenciales = [
            'admin' => 'Admin123!',
            'supervisor' => 'Supervisor123!',
            'operario' => 'Operario123!',
        ];

        return view('superadmin.tenants.show', compact('tenant', 'sizeMB', 'backupInfo', 'webhooks', 'demoCredenciales'));
    }

    public function backupTenant(Request $request, Tenant $tenant)
    {
        $path = TenantBackupService::backup($tenant);

        $request->attributes->set('admin_log_descripcion', "Generó backup manual para tenant {$tenant->rut}");

        return back()->with('success', 'Backup generado correctamente: ' . basename($path));
    }

    public function downloadTenantBackup(Request $request, Tenant $tenant)
    {
        $backup = TenantBackupService::lastBackupPath($tenant);

        if (!$backup || !File::exists($backup)) {
            return back()->with('error', 'No hay backups disponibles.');
        }

        return response()->download($backup);
    }

    public function archiveTenant(ArchiveTenantRequest $request, Tenant $tenant)
    {
        $request->validate([
            'confirm_rut' => "in:{$tenant->rut}",
        ], [
            'confirm_rut.in' => 'Debes escribir exactamente el RUT para confirmar.',
        ]);

        $archived = TenantBackupService::archive($tenant);

        if ($archived['sqlite']) {
            $tenant->sqlite_path = $archived['sqlite'];
        }

        $tenant->estado = 'eliminado';
        if (Schema::hasColumn('tenants', 'ultima_respaldo')) {
            $tenant->ultima_respaldo = now();
        }
        $tenant->save();
        $tenant->delete();

        $request->attributes->set('admin_log_descripcion', "Archivó tenant {$tenant->rut}");

        return redirect()->route('superadmin.tenants.index')
            ->with('success', 'Tenant archivado. Backup: ' . basename($archived['backup']));
    }

    public function storeTenant(Request $request)
    {
        $data = $request->validate([
            'rut' => 'required|string|max:20|unique:tenants,rut',
            'nombre_comercial' => 'required|string|max:255',
            'nombre_contacto' => 'nullable|string|max:255',
            'email_contacto' => 'nullable|email|max:255',
            'telefono_contacto' => 'nullable|string|max:50',
            'direccion_contacto' => 'nullable|string|max:500',
        ]);

        $data['api_key'] = Tenant::generarApiKey();
        $data['estado'] = 'activo';
        $data['formato_factura'] = 'efactura';
        $data['sqlite_path'] = storage_path("tenants/{$data['rut']}.sqlite");

        $tenant = Tenant::create($data);

        $request->attributes->set('admin_log_descripcion', "Creó tenant {$tenant->rut}");

        $this->ensureTenantDatabase($tenant);
        $credenciales = $this->seedDefaultUsers($tenant, false);

        $mensaje = collect($credenciales)
            ->map(fn($datos) => "Usuario: {$datos['username']} | Email: {$datos['email']} | Contraseña: {$datos['password']} | Rol: {$datos['rol']}")
            ->implode("<br>");

        return back()->with('success', "Tenant creado correctamente.<br><pre class='mb-0'>{$mensaje}</pre>");
    }

    public function updateTenant(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'nombre_comercial' => 'required|string|max:255',
            'estado' => 'required|in:activo,suspendido,eliminado',
            'formato_factura' => 'required|string|max:50',
            'nombre_contacto' => 'nullable|string|max:255',
            'email_contacto' => 'nullable|email|max:255',
            'telefono_contacto' => 'nullable|string|max:50',
            'direccion_contacto' => 'nullable|string|max:500',
        ]);

        $tenant->update($data);

        $request->attributes->set('admin_log_descripcion', "Actualizó tenant {$tenant->rut}");

        return back()->with('success', 'Tenant actualizado correctamente.');
    }

    public function regenerateTenantKey(Request $request, Tenant $tenant)
    {
        $tenant->api_key = Tenant::generarApiKey();
        $tenant->save();

        $request->attributes->set('admin_log_descripcion', "Regeneró API Key para tenant {$tenant->rut}");

        return back()->with('success', 'API Key regenerada correctamente.');
    }

    public function toggleTenant(Request $request, Tenant $tenant)
    {
        $tenant->estado = $tenant->estado === 'activo' ? 'suspendido' : 'activo';
        $tenant->save();

        $request->attributes->set('admin_log_descripcion', "Cambió estado de tenant {$tenant->rut} a {$tenant->estado}");

        return back()->with('success', 'Estado del tenant actualizado.');
    }

    public function seedTenantUsers(Request $request, Tenant $tenant)
    {
        $this->ensureTenantDatabase($tenant, true);

        $credenciales = $this->seedDefaultUsers($tenant, true);

        $mensaje = collect($credenciales)
            ->map(fn($datos) => "Usuario: {$datos['username']} | Email: {$datos['email']} | Contraseña: {$datos['password']} | Rol: {$datos['rol']}")
            ->implode("<br>");

        return back()->with('success', "Usuarios generados.<br><pre class='mb-0'>{$mensaje}</pre>");
    }

    public function ensureTenantDbManually(Request $request, Tenant $tenant)
    {
        $this->ensureTenantDatabase($tenant, true);

        $request->attributes->set('admin_log_descripcion', "Re-ejecutó migraciones para tenant {$tenant->rut}");

        return back()->with('success', 'Migraciones ejecutadas correctamente en la base del tenant.');
    }

    public function webhooks(Request $request)
    {
        $webhooks = DB::table('webhook_inbox_global')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('superadmin.webhooks.index', compact('webhooks'));
    }

    public function testEmail(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email'
        ]);

        $config = SystemConfig::getEmailConfig();

        try {
            config([
                'mail.mailers.smtp.host' => $config['smtp_host'] ?? null,
                'mail.mailers.smtp.port' => $config['smtp_port'] ?? 587,
                'mail.mailers.smtp.username' => $config['smtp_user'] ?? null,
                'mail.mailers.smtp.password' => $config['smtp_pass'] ?? null,
                'mail.mailers.smtp.encryption' => $config['smtp_encryption'] ?? null,
                'mail.from.address' => $config['from_address'] ?? 'no-reply@puntos.local',
                'mail.from.name' => $config['from_name'] ?? 'Sistema de Puntos',
            ]);

            Mail::mailer('smtp')
                ->to($data['email'])
                ->send(new \App\Mail\TestEmailSmtp());

            return back()->with('success', 'Email de prueba enviado correctamente. Revisa tu bandeja.');
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo enviar el email de prueba: ' . $e->getMessage());
        }
    }

    public function testWhatsApp(Request $request)
    {
        $data = $request->validate([
            'telefono' => 'required|string|max:30',
        ]);

        $config = SystemConfig::getWhatsAppConfig();

        if (!($config['activo'] ?? false)) {
            return back()->with('error', 'El servicio de WhatsApp está deshabilitado. Actívalo y guarda la configuración antes de probar.');
        }

        if (empty($config['url']) || empty($config['token'])) {
            return back()->with('error', 'Debes configurar URL y Token de WhatsApp antes de enviar pruebas.');
        }

        try {
            // Limpiar y normalizar teléfono
            $telefonoLimpio = preg_replace('/[^0-9]/', '', $data['telefono']);
            
            // Si empieza con 09 y tiene 9 dígitos (formato local UY), convertir a internacional
            if (preg_match('/^09\d{7}$/', $telefonoLimpio)) {
                $telefonoLimpio = '598' . substr($telefonoLimpio, 1);
            }
            $mensaje = '✅ Prueba exitosa de WhatsApp desde Sistema de Puntos. Configuración confirmada.';
            
            // Construir URL con parámetros (igual que Google Apps Script)
            $urlConParams = $config['url'] 
                . '?token=' . urlencode($config['token'])
                . '&number=' . urlencode($telefonoLimpio)
                . '&message=' . urlencode($mensaje)
                . '&urlDocument=';

            $response = Http::timeout(10)->get($urlConParams);

            if (!$response->successful()) {
                throw new \RuntimeException('Respuesta HTTP ' . $response->status() . ': ' . $response->body());
            }

            return back()->with('success', 'Mensaje de prueba enviado correctamente al número ' . $data['telefono']);
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo enviar el WhatsApp de prueba: ' . $e->getMessage());
        }
    }

    private function ensureTenantDatabase(Tenant $tenant, bool $force = false): void
    {
        $sqlitePath = $tenant->getSqlitePath();

        if (!File::exists(dirname($sqlitePath))) {
            File::makeDirectory(dirname($sqlitePath), 0755, true);
        }

        if (!File::exists($sqlitePath)) {
            File::put($sqlitePath, '');
        } elseif (!$force) {
            config([
                'database.connections.tenant_temp' => [
                    'driver' => 'sqlite',
                    'database' => $sqlitePath,
                    'prefix' => '',
                    'foreign_key_constraints' => true,
                ],
            ]);

            DB::purge('tenant_temp');
            DB::setDefaultConnection('tenant_temp');

            $hasClientes = Schema::connection('tenant_temp')->hasTable('clientes');
            $hasUsuarios = Schema::connection('tenant_temp')->hasTable('usuarios');
            $hasFacturas = Schema::connection('tenant_temp')->hasTable('facturas');

            DB::setDefaultConnection('mysql');
            DB::purge('tenant_temp');

            if ($hasClientes && $hasUsuarios && $hasFacturas) {
                return;
            }
        }

        config([
            'database.connections.tenant_temp' => [
                'driver' => 'sqlite',
                'database' => $sqlitePath,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        DB::purge('tenant_temp');

        Artisan::call('migrate', [
            '--database' => 'tenant_temp',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        DB::purge('tenant_temp');
        DB::setDefaultConnection('mysql');

        if (Schema::hasColumn('tenants', 'ultima_migracion')) {
            $tenant->ultima_migracion = now();
            $tenant->save();
        }
    }

    private function seedDefaultUsers(Tenant $tenant, bool $force = true): array
    {
        $suffix = $tenant->usernameSuffix();

        $sqlitePath = $tenant->getSqlitePath();
        config([
            'database.connections.tenant_temp' => [
                'driver' => 'sqlite',
                'database' => $sqlitePath,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        DB::purge('tenant_temp');
        DB::setDefaultConnection('tenant_temp');

        $passwords = config('tenant.default_passwords');
        $emailDomain = config('tenant.default_email_domain');

        $users = [
            [
                'nombre' => 'Admin ' . $tenant->nombre_comercial,
                'username' => 'admin' . $suffix,
                'password' => $passwords['admin'],
                'rol' => 'admin',
                'email' => 'admin@' . $emailDomain,
            ],
            [
                'nombre' => 'Supervisor ' . $tenant->nombre_comercial,
                'username' => 'supervisor' . $suffix,
                'password' => $passwords['supervisor'],
                'rol' => 'supervisor',
                'email' => 'supervisor@' . $emailDomain,
            ],
            [
                'nombre' => 'Operario ' . $tenant->nombre_comercial,
                'username' => 'operario' . $suffix,
                'password' => $passwords['operario'],
                'rol' => 'operario',
                'email' => 'operario@' . $emailDomain,
            ],
        ];

        $credenciales = [];
        foreach ($users as $user) {
            if (!$force && DB::table('usuarios')->where('username', $user['username'])->exists()) {
                $credenciales[] = [
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'password' => $user['password'],
                    'rol' => $user['rol'],
                ];
                continue;
            }

            DB::table('usuarios')->updateOrInsert(
                ['username' => $user['username']],
                [
                    'nombre' => $user['nombre'],
                    'email' => $user['email'],
                    'password' => Hash::make($user['password']),
                    'rol' => $user['rol'],
                    'activo' => 1,
                    'ultimo_acceso' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $credenciales[] = [
                'username' => $user['username'],
                'email' => $user['email'],
                'password' => $user['password'],
                'rol' => $user['rol'],
            ];
        }

        DB::setDefaultConnection('mysql');
        DB::purge('tenant_temp');

        return $credenciales;
    }
}
