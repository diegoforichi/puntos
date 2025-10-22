<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\Tenant;

class GenerateTenantApiTokens extends Command
{
    protected $signature = 'tenants:generate-api-tokens {--force : Regenerar tokens incluso si ya existen}';

    protected $description = 'Genera tokens de API para tenants que no tienen uno asignado.';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $tenants = Tenant::query()
            ->when(!$force, fn ($q) => $q->whereNull('api_token'))
            ->get();

        if ($tenants->isEmpty()) {
            $this->info('No hay tenants pendientes de token.');
            return self::SUCCESS;
        }

        $tenants->each(function (Tenant $tenant) use ($force) {
            $token = $this->generateToken();
            $tenant->forceFill([
                'api_token' => $token,
                'api_token_last_used_at' => null,
            ])->save();

            $this->line("✔ Token asignado a {$tenant->rut}: {$token}" . ($force ? ' (regenerado)' : ''));
        });

        $this->info('Proceso completado.');

        return self::SUCCESS;
    }

    private function generateToken(): string
    {
        return 'pk_' . Str::random(60);
    }
}

