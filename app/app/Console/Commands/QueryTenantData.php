<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class QueryTenantData extends Command
{
    protected $signature = 'tenant:query {rut}';
    protected $description = 'Consultar datos del tenant';

    public function handle()
    {
        $rut = $this->argument('rut');
        $tenant = Tenant::where('rut', $rut)->first();
        
        if (!$tenant) {
            $this->error("Tenant no encontrado");
            return 1;
        }
        
        // Conectar a SQLite
        config([
            'database.connections.tenant' => [
                'driver' => 'sqlite',
                'database' => $tenant->getSqlitePath(),
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]
        ]);
        
        DB::purge('tenant');
        DB::setDefaultConnection('tenant');
        
        // Consultar clientes
        $clientes = DB::table('clientes')->get();
        $facturas = DB::table('facturas')->get();
        
        $this->info("📊 Datos del Tenant: {$tenant->nombre_comercial}");
        $this->line("");
        $this->info("👥 Clientes ({$clientes->count()}):");
        foreach ($clientes as $cliente) {
            $this->line("   - {$cliente->nombre} ({$cliente->documento})");
            $this->line("     Puntos: {$cliente->puntos_acumulados}");
            $this->line("     Teléfono: " . ($cliente->telefono ?? 'N/A'));
        }
        
        $this->line("");
        $this->info("📄 Facturas ({$facturas->count()}):");
        foreach ($facturas as $factura) {
            $this->line("   - Factura {$factura->numero_factura}");
            $this->line("     Monto: \${$factura->monto_total}");
            $this->line("     Puntos generados: {$factura->puntos_generados}");
        }
        
        return 0;
    }
}
