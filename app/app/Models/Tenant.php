<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rut',
        'nombre_comercial',
        'api_key',
        'estado',
        'sqlite_path',
        'nombre_contacto',
        'email_contacto',
        'telefono_contacto',
        'direccion_contacto',
        'formato_factura',
        'ultimo_webhook',
        'facturas_recibidas',
        'puntos_generados_total',
        'ultima_respaldo',
        'ultima_migracion',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'ultimo_webhook' => 'datetime',
        'ultima_respaldo' => 'datetime',
        'ultima_migracion' => 'datetime',
    ];

    public function usernameSuffix(): string
    {
        $digits = preg_replace('/[^0-9]/', '', (string) $this->rut);
        $suffix = substr($digits, -4);

        if (!$suffix) {
            return '0001';
        }

        return str_pad($suffix, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generar API Key única para el tenant
     */
    public static function generarApiKey(): string
    {
        return 'tk_' . Str::random(40);
    }

    /**
     * Obtener la ruta del archivo SQLite del tenant
     */
    public function getSqlitePath(): string
    {
        return storage_path("tenants/{$this->rut}.sqlite");
    }

    /**
     * Verificar si el tenant está activo
     */
    public function isActivo(): bool
    {
        return $this->estado === 'activo';
    }

    /**
     * Scope para tenants activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}
