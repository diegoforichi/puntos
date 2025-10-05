<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PuntosController;
use App\Http\Controllers\AutoconsultaController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\SuperAdmin\AuthController as SuperAdminAuthController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Rutas del sistema multitenant de puntos
|
| Estructura: /{tenant}/ruta
| Ejemplo: /000000000016/dashboard
|
*/

// Página de inicio (landing)
Route::get('/', function () {
    return view('landing');
});

// Rutas SuperAdmin (configuración global)
Route::prefix('superadmin')->group(function () {
    Route::middleware('superadmin.guest')->group(function () {
        Route::get('/login', [SuperAdminAuthController::class, 'showLogin'])->name('superadmin.login');
        Route::post('/login', [SuperAdminAuthController::class, 'login'])->name('superadmin.login.submit');
    });

    Route::middleware(['auth:superadmin', 'superadmin.auth'])->group(function () {
        Route::post('/logout', [SuperAdminAuthController::class, 'logout'])->name('superadmin.logout');

        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');

        Route::get('/config', [SuperAdminController::class, 'config'])->name('superadmin.config');
        Route::post('/config/email', [SuperAdminController::class, 'saveEmailConfig'])->name('superadmin.config.email');
        Route::post('/config/whatsapp', [SuperAdminController::class, 'saveWhatsAppConfig'])->name('superadmin.config.whatsapp');
        Route::post('/config/email/test', [SuperAdminController::class, 'testEmail'])->name('superadmin.config.email.test');
        Route::post('/config/whatsapp/test', [SuperAdminController::class, 'testWhatsApp'])->name('superadmin.config.whatsapp.test');

        Route::get('/tenants', [SuperAdminController::class, 'tenants'])->name('superadmin.tenants.index');
        Route::post('/tenants', [SuperAdminController::class, 'storeTenant'])->name('superadmin.tenants.store');
        Route::get('/tenants/{tenant}', [SuperAdminController::class, 'showTenant'])->name('superadmin.tenants.show');
        Route::post('/tenants/{tenant}/backup', [SuperAdminController::class, 'backupTenant'])->name('superadmin.tenants.backup');
        Route::get('/tenants/{tenant}/backup/download', [SuperAdminController::class, 'downloadTenantBackup'])->name('superadmin.tenants.download-backup');
        Route::post('/tenants/{tenant}/archive', [SuperAdminController::class, 'archiveTenant'])->name('superadmin.tenants.archive');
        Route::put('/tenants/{tenant}', [SuperAdminController::class, 'updateTenant'])->name('superadmin.tenants.update');
        Route::post('/tenants/{tenant}/regenerate-key', [SuperAdminController::class, 'regenerateTenantKey'])->name('superadmin.tenants.regenerate');
        Route::post('/tenants/{tenant}/toggle', [SuperAdminController::class, 'toggleTenant'])->name('superadmin.tenants.toggle');
        Route::post('/tenants/{tenant}/seed-users', [SuperAdminController::class, 'seedTenantUsers'])->name('superadmin.tenants.seed-users');
        Route::post('/tenants/{tenant}/ensure-db', [SuperAdminController::class, 'ensureTenantDbManually'])->name('superadmin.tenants.ensure-db');
        Route::post('/tenants/{tenant}/test-webhook', \App\Http\Controllers\SuperAdmin\TenantTestWebhookController::class)->name('superadmin.tenants.test-webhook');

        Route::get('/webhooks', [SuperAdminController::class, 'webhooks'])->name('superadmin.webhooks');
    });
});

/*
|--------------------------------------------------------------------------
| Rutas del Tenant (requieren identificación)
|--------------------------------------------------------------------------
*/

Route::prefix('{tenant}')->middleware(['tenant'])->group(function () {
    
    // Rutas de autenticación (NO requieren estar logueado)
    Route::get('/login', [AuthController::class, 'showLogin'])->name('tenant.login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('tenant.logout');
    
    // Rutas protegidas (requieren autenticación)
    Route::middleware(['auth.tenant'])->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');
        
        // Clientes (todos los roles)
        Route::get('/clientes', [ClienteController::class, 'index'])->name('tenant.clientes');
        Route::get('/clientes/buscar', [ClienteController::class, 'buscar'])->name('tenant.clientes.buscar');
        Route::get('/clientes/{id}', [ClienteController::class, 'show'])->name('tenant.clientes.show');
        Route::get('/clientes/{id}/editar', [ClienteController::class, 'edit'])->name('tenant.clientes.edit');
        Route::put('/clientes/{id}', [ClienteController::class, 'update'])->name('tenant.clientes.update');
        Route::get('/clientes/{id}/facturas', [ClienteController::class, 'facturas'])->name('tenant.clientes.facturas');
        
            // Reportes (todos los roles)
            Route::get('/reportes', [ReporteController::class, 'index'])->name('tenant.reportes');
            Route::get('/reportes/clientes', [ReporteController::class, 'clientes'])->name('tenant.reportes.clientes');
            Route::get('/reportes/facturas', [ReporteController::class, 'facturas'])->name('tenant.reportes.facturas');
            Route::get('/reportes/canjes', [ReporteController::class, 'canjes'])->name('tenant.reportes.canjes');
            Route::get('/reportes/actividades', [ReporteController::class, 'actividades'])->name('tenant.reportes.actividades');
        
        // Canje de puntos (admin y supervisor)
        Route::middleware(['role:admin,supervisor'])->group(function () {
            Route::get('/puntos/canjear', [PuntosController::class, 'mostrarFormulario'])->name('tenant.puntos.canjear');
            Route::post('/puntos/buscar-cliente', [PuntosController::class, 'buscarCliente'])->name('tenant.puntos.buscar');
            Route::post('/puntos/canjear', [PuntosController::class, 'procesar'])->name('tenant.puntos.procesar');
            Route::get('/puntos/cupon/{id}', [PuntosController::class, 'mostrarCupon'])->name('tenant.puntos.cupon');
            Route::get('/puntos/cupon/{id}/pdf', [PuntosController::class, 'descargarCuponPdf'])->name('tenant.puntos.cupon.pdf');
        });
        
        // Rutas solo para admin
        Route::middleware(['role:admin'])->group(function () {
            // Promociones
            Route::get('/promociones', [PromocionController::class, 'index'])->name('tenant.promociones');
            Route::get('/promociones/crear', [PromocionController::class, 'create'])->name('tenant.promociones.crear');
            Route::post('/promociones', [PromocionController::class, 'store'])->name('tenant.promociones.store');
            Route::get('/promociones/{id}/editar', [PromocionController::class, 'edit'])->name('tenant.promociones.edit');
            Route::put('/promociones/{id}', [PromocionController::class, 'update'])->name('tenant.promociones.update');
            Route::post('/promociones/{id}/toggle', [PromocionController::class, 'toggle'])->name('tenant.promociones.toggle');
            Route::delete('/promociones/{id}', [PromocionController::class, 'destroy'])->name('tenant.promociones.destroy');
            
            // Usuarios
            Route::get('/usuarios', [UsuarioController::class, 'index'])->name('tenant.usuarios');
            Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('tenant.usuarios.crear');
            Route::post('/usuarios', [UsuarioController::class, 'store'])->name('tenant.usuarios.store');
            Route::get('/usuarios/{id}/editar', [UsuarioController::class, 'edit'])->name('tenant.usuarios.edit');
            Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('tenant.usuarios.update');
            Route::post('/usuarios/{id}/cambiar-password', [UsuarioController::class, 'cambiarPassword'])->name('tenant.usuarios.password');
            Route::post('/usuarios/{id}/toggle', [UsuarioController::class, 'toggle'])->name('tenant.usuarios.toggle');
            
            // Configuración
            Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('tenant.configuracion');
            Route::post('/configuracion/puntos', [ConfiguracionController::class, 'actualizarPuntos'])->name('tenant.configuracion.puntos');
            Route::post('/configuracion/vencimiento', [ConfiguracionController::class, 'actualizarVencimiento'])->name('tenant.configuracion.vencimiento');
            Route::post('/configuracion/contacto', [ConfiguracionController::class, 'actualizarContacto'])->name('tenant.configuracion.contacto');
            Route::post('/configuracion/whatsapp', [ConfiguracionController::class, 'actualizarWhatsApp'])->name('tenant.configuracion.whatsapp');
        Route::post('/configuracion/acumulacion', [ConfiguracionController::class, 'actualizarAcumulacion'])->name('tenant.configuracion.acumulacion');
        Route::post('/configuracion/moneda', [ConfiguracionController::class, 'actualizarMoneda'])->name('tenant.configuracion.moneda');
            Route::post('/configuracion/compactar', [ConfiguracionController::class, 'compactarBaseDatos'])->name('tenant.configuracion.compactar');
        });
    });
    
    // Portal público de autoconsulta (NO requiere autenticación)
    Route::get('/consulta', [AutoconsultaController::class, 'index'])->name('tenant.consulta');
    Route::post('/consulta', [AutoconsultaController::class, 'consultar'])->name('tenant.consulta.buscar');
    Route::post('/consulta/actualizar-contacto', [AutoconsultaController::class, 'actualizarContacto'])->name('tenant.consulta.contacto');
});
