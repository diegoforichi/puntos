<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TenantMaintenanceDaily extends Command
{
    protected $signature = 'tenant:tareas-diarias {--tenant=} {--grace-days=0 : Días adicionales de gracia para vencer puntos}';

    protected $description = 'Ejecuta mantenimiento diario: vencer puntos, notificar y enviar reportes';

    public function handle(): int
    {
        $tenant = $this->option('tenant');
        $graceDays = (int) $this->option('grace-days');

        $comandos = [
            ['cmd' => 'puntos:expirar', 'params' => ['--tenant' => $tenant, '--days' => $graceDays], 'titulo' => 'Vencimiento automático de puntos'],
            ['cmd' => 'puntos:notificar-vencimiento', 'params' => ['--tenant' => $tenant], 'titulo' => 'Notificación de puntos por vencer'],
            ['cmd' => 'tenant:send-daily-reports', 'params' => [], 'titulo' => 'Email de resumen diario'],
        ];

        $this->info('Iniciando tareas diarias del sistema de puntos...');

        foreach ($comandos as $bloque) {
            $titulo = $bloque['titulo'];
            $comando = $bloque['cmd'];
            $params = array_filter($bloque['params'], fn ($v) => $v !== null);

            try {
                $this->line("\n▶ Ejecutando {$titulo} ({$comando})");

                $exitCode = $this->call($comando, $params);

                if ($exitCode !== 0) {
                    $this->warn("⚠ El comando {$comando} finalizó con código {$exitCode}");
                } else {
                    $this->info("✔ {$titulo} completado");
                }

            } catch (\Throwable $e) {
                Log::error('Error en tarea diaria', [
                    'comando' => $comando,
                    'error' => $e->getMessage(),
                ]);

                $this->error("❌ Error ejecutando {$comando}: {$e->getMessage()}");
            }
        }

        $this->line('\n--------------------------------------------------');
        $this->info('Tareas diarias completadas');

        return self::SUCCESS;
    }
}
