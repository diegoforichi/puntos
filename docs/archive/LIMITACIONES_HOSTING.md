# Limitaciones del Hosting y Dependencias

## Fecha: 2025-09-29

## Limitaciones del Hosting

### Versiones de PHP
- **PHP 8.1+**: Verificar compatibilidad del hosting
- **Extensiones requeridas**: PDO, MySQL, JSON, OpenSSL
- **Memory limit**: Mínimo 128MB recomendado
- **Execution time**: Para procesamiento de webhooks

### Base de Datos
- **MySQL 5.7+** o **MySQL 8.0+**: Para producción
- **SQLite 3.35+**: Para desarrollo local
- **Conexiones concurrentes**: Verificar límites del hosting
- **Storage**: Espacio para múltiples bases de datos

### Servidor Web
- **Apache 2.4+** o **Nginx 1.18+**
- **mod_rewrite**: Para URLs amigables de Laravel
- **SSL/TLS**: Certificado válido para webhooks
- **Cron jobs**: Para tareas programadas (vencimiento de puntos)

## Estrategia de Dependencias Incluidas

### Laravel Framework
```json
{
  "require": {
    "laravel/framework": "^10.0",
    "laravel/sanctum": "^3.0"
  }
}
```

### Dependencias Incluidas en el Proyecto
- **Composer**: Incluir `vendor/` en el repositorio
- **Node.js**: Solo para build de assets (opcional)
- **Bootstrap**: CDN o archivos locales
- **jQuery**: Solo si es necesario

### Estructura de Dependencias
```
puntos_system/
├── vendor/                 # Composer dependencies
├── public/
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   └── app.css
│   └── js/
│       ├── bootstrap.min.js
│       └── app.js
├── resources/
│   ├── views/
│   └── lang/
└── storage/
```

## Configuración de Producción

### Variables de Entorno
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=puntos_system
DB_USERNAME=usuario
DB_PASSWORD=password

# Para desarrollo local
DB_CONNECTION_SQLITE=sqlite
DB_DATABASE_SQLITE=database/database.sqlite
```

### Optimizaciones para Hosting Compartido
```php
// config/app.php
'debug' => env('APP_DEBUG', false),
'url' => env('APP_URL', 'https://tu-dominio.com'),

// config/database.php
'default' => env('DB_CONNECTION', 'mysql'),
'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'puntos_system'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
    'sqlite' => [
        'driver' => 'sqlite',
        'database' => env('DB_DATABASE_SQLITE', database_path('database.sqlite')),
        'prefix' => '',
    ],
],
```

## Tareas Programadas (Cron)

### Configuración de Cron
```bash
# En el hosting, agregar al crontab
* * * * * cd /path/to/puntos_system && php artisan schedule:run >> /dev/null 2>&1
```

### Tareas de Laravel
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Eliminar puntos vencidos diariamente
    $schedule->call(function () {
        $this->eliminarPuntosVencidos();
    })->daily();
    
    // Backup de bases de datos
    $schedule->call(function () {
        $this->backupDatabases();
    })->dailyAt('02:00');
}
```

## Backup y Recuperación

### Estrategia de Backup
```php
// app/Console/Commands/BackupCommand.php
class BackupCommand extends Command
{
    public function handle()
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            $this->backupTenantDatabase($tenant);
        }
    }
    
    private function backupTenantDatabase($tenant)
    {
        $filename = "backup_{$tenant->database_name}_" . date('Y-m-d_H-i-s') . ".sql";
        $path = storage_path("backups/{$filename}");
        
        // Comando mysqldump
        $command = "mysqldump -u {$this->dbUser} -p{$this->dbPass} {$tenant->database_name} > {$path}";
        exec($command);
    }
}
```

## Monitoreo y Logs

### Configuración de Logs
```php
// config/logging.php
'channels' => [
    'webhook' => [
        'driver' => 'daily',
        'path' => storage_path('logs/webhook.log'),
        'level' => 'info',
        'days' => 30,
    ],
    'tenant' => [
        'driver' => 'daily',
        'path' => storage_path('logs/tenant.log'),
        'level' => 'info',
        'days' => 30,
    ],
],
```

### Health Check Endpoint
```php
// routes/api.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => config('app.version'),
    ]);
});
```

## Consideraciones de Seguridad

### Headers de Seguridad
```php
// app/Http/Middleware/SecurityHeaders.php
public function handle($request, Closure $next)
{
    $response = $next($request);
    
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-Frame-Options', 'DENY');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    
    return $response;
}
```

### Rate Limiting
```php
// app/Http/Middleware/ThrottleWebhook.php
public function handle($request, Closure $next)
{
    $key = $request->ip() . ':' . $request->route('tenant');
    
    if (RateLimiter::tooManyAttempts($key, 100)) {
        return response()->json(['error' => 'Too many requests'], 429);
    }
    
    RateLimiter::hit($key, 60); // 100 requests per minute
    
    return $next($request);
}
```
