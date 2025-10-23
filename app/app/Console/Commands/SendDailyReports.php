<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Mail\ResumenDiario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Services\NotificationConfigResolver;

class SendDailyReports extends Command
{
    protected $signature = 'tenant:send-daily-reports';
    protected $description = 'Envía resumen diario de actividad por email a todos los tenants activos';

    public function __construct(private NotificationConfigResolver $configResolver)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $tenants = Tenant::where('estado', 'activo')->get();

        $this->info("Enviando reportes diarios a {$tenants->count()} tenant(s)...");

        $enviados = 0;
        $fallidos = 0;

        foreach ($tenants as $tenant) {
            try {
                $emailContacto = $tenant->email_contacto;

                if (empty($emailContacto)) {
                    $this->warn("  ⚠️  Tenant {$tenant->rut}: sin email de contacto, omitido.");
                    continue;
                }

                $stats = $this->obtenerEstadisticas($tenant);

                $config = $this->configResolver->resolveEmailConfig($tenant);

                Mail::mailer('smtp')->setSymfonyMailer(null);
                config([
                    'mail.mailers.smtp.host' => $config['host'] ?? null,
                    'mail.mailers.smtp.port' => $config['port'] ?? 587,
                    'mail.mailers.smtp.username' => $config['username'] ?? null,
                    'mail.mailers.smtp.password' => $config['password'] ?? null,
                    'mail.mailers.smtp.encryption' => $config['encryption'] ?? null,
                    'mail.from.address' => $config['from_address'] ?? config('mail.from.address'),
                    'mail.from.name' => $config['from_name'] ?? config('mail.from.name'),
                ]);

                Mail::to($emailContacto)->send(new ResumenDiario($tenant, $stats));

                $this->info("  ✅ {$tenant->rut}: enviado a {$emailContacto}");
                $enviados++;

            } catch (\Throwable $e) {
                $this->error("  ❌ {$tenant->rut}: {$e->getMessage()}");
                $fallidos++;
            }
        }

        $this->info("\n📊 Resumen: {$enviados} enviados, {$fallidos} fallidos.");

        return $fallidos > 0 ? 1 : 0;
    }

    private function obtenerEstadisticas(Tenant $tenant): array
    {
        $sqlitePath = $tenant->getSqlitePath();
        config([
            'database.connections.tenant_temp' => [
                'driver' => 'sqlite',
                'database' => $sqlitePath,
                'prefix' => '',
            ],
        ]);

        DB::purge('tenant_temp');

        $ayer = now()->subDay()->startOfDay();
        $hoy = now()->startOfDay();

        $facturasHoy = DB::connection('tenant_temp')->table('facturas')
            ->whereBetween('created_at', [$ayer, $hoy])
            ->count();

        $puntosGeneradosHoy = DB::connection('tenant_temp')->table('facturas')
            ->whereBetween('created_at', [$ayer, $hoy])
            ->sum('puntos_generados');

        $puntosCanjeadosHoy = DB::connection('tenant_temp')->table('puntos_canjeados')
            ->whereBetween('created_at', [$ayer, $hoy])
            ->sum('puntos_canjeados');

        $nuevosClientesHoy = DB::connection('tenant_temp')->table('clientes')
            ->whereBetween('created_at', [$ayer, $hoy])
            ->count();

        $totalClientes = DB::connection('tenant_temp')->table('clientes')->count();
        $puntosCirculacion = DB::connection('tenant_temp')->table('clientes')->sum('puntos_acumulados');
        $factursMes = DB::connection('tenant_temp')->table('facturas')
            ->whereMonth('created_at', now()->month)
            ->count();

        $clientesPorVencer = DB::connection('tenant_temp')->table('facturas')
            ->where('fecha_vencimiento', '<=', now()->addDays(7))
            ->where('fecha_vencimiento', '>', now())
            ->distinct('cliente_id')
            ->count('cliente_id');

        DB::purge('tenant_temp');
        DB::setDefaultConnection('mysql');

        return [
            'facturas_hoy' => $facturasHoy,
            'puntos_generados_hoy' => $puntosGeneradosHoy,
            'puntos_canjeados_hoy' => $puntosCanjeadosHoy,
            'nuevos_clientes_hoy' => $nuevosClientesHoy,
            'total_clientes' => $totalClientes,
            'puntos_circulacion' => $puntosCirculacion,
            'facturas_mes' => $factursMes,
            'clientes_por_vencer' => $clientesPorVencer,
        ];
    }
}

