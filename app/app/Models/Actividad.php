<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Actividad
 *
 * Representa una acción registrada en el sistema
 * Log de auditoría de todas las operaciones
 *
 * Tabla: actividades (SQLite del tenant)
 */
class Actividad extends Model
{
    /**
     * Nombre de la tabla
     */
    protected $table = 'actividades';

    protected $connection = 'tenant';

    /**
     * Campos asignables en masa
     */
    protected $fillable = [
        'usuario_id',
        'accion',
        'descripcion',
        'datos_json',
    ];

    /**
     * Campos que deben ser casteados
     */
    protected $casts = [
        'usuario_id' => 'integer',
        'datos_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Acciones comunes del sistema
     */
    const ACCION_LOGIN = 'login';

    const ACCION_LOGOUT = 'logout';

    const ACCION_CANJE = 'canje_puntos';

    const ACCION_FACTURA = 'factura_procesada';

    const ACCION_CLIENTE_CREADO = 'cliente_creado';

    const ACCION_CONFIG = 'configuracion_actualizada';

    const ACCION_PROMOCION = 'promocion_gestionada';

    const ACCION_USUARIO = 'usuario_gestionado';

    const ACCION_CAMPANIA = 'campania_gestionada';

    const ACCION_AJUSTE = 'ajuste_puntos';

    /**
     * Relación: Actividad pertenece a un usuario
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Scope: Actividades de hoy
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: Actividades del mes actual
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDelMes($query)
    {
        return $query->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'));
    }

    /**
     * Scope: Actividades entre fechas
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $desde
     * @param  string  $hasta
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('created_at', [$desde, $hasta]);
    }

    /**
     * Scope: Filtrar por acción
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $accion
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    /**
     * Scope: Filtrar por usuario
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $usuarioId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDeUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    /**
     * Registrar una nueva actividad
     *
     * @param  int|null  $usuarioId
     * @param  string  $accion
     * @param  string  $descripcion
     * @param  array  $datos
     * @return self
     */
    public static function registrar($usuarioId, $accion, $descripcion, $datos = [])
    {
        return self::create([
            'usuario_id' => $usuarioId,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'datos_json' => array_merge($datos, [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]),
        ]);
    }

    /**
     * Obtener icono según el tipo de acción
     *
     * @return string
     */
    public function getIconoAttribute()
    {
        return match ($this->accion) {
            self::ACCION_LOGIN => 'bi-box-arrow-in-right',
            self::ACCION_LOGOUT => 'bi-box-arrow-right',
            self::ACCION_CANJE => 'bi-gift',
            self::ACCION_FACTURA => 'bi-receipt',
            self::ACCION_CLIENTE_CREADO => 'bi-person-plus',
            self::ACCION_CONFIG => 'bi-gear',
            self::ACCION_PROMOCION => 'bi-tags',
            self::ACCION_USUARIO => 'bi-person-badge',
            self::ACCION_CAMPANIA => 'bi-megaphone',
            self::ACCION_AJUSTE => 'bi-sliders',
            default => 'bi-circle',
        };
    }

    /**
     * Obtener color según el tipo de acción
     *
     * @return string
     */
    public function getColorAttribute()
    {
        return match ($this->accion) {
            self::ACCION_LOGIN => 'text-success',
            self::ACCION_LOGOUT => 'text-secondary',
            self::ACCION_CANJE => 'text-primary',
            self::ACCION_FACTURA => 'text-info',
            self::ACCION_CLIENTE_CREADO => 'text-success',
            self::ACCION_CONFIG => 'text-warning',
            self::ACCION_AJUSTE => 'text-info',
            default => 'text-muted',
        };
    }
}
