<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo PuntosCanjeado
 *
 * Representa un registro de canje de puntos
 * Almacena histórico de todas las redenciones
 *
 * Tabla: puntos_canjeados (SQLite del tenant)
 */
class PuntosCanjeado extends Model
{
    /**
     * Nombre de la tabla
     */
    protected $table = 'puntos_canjeados';

    /**
     * Campos asignables en masa
     */
    protected $fillable = [
        'cliente_id',
        'puntos_canjeados',
        'puntos_restantes',
        'concepto',
        'autorizado_por',
        'origen',
        'referencia',
    ];

    /**
     * Campos que deben ser casteados
     */
    protected $casts = [
        'cliente_id' => 'integer',
        'puntos_canjeados' => 'decimal:2',
        'puntos_restantes' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (! $model->origen) {
                $model->origen = 'panel';
            }
        });
    }

    /**
     * Relación: Canje pertenece a un cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function autorizadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'autorizado_por')
            ->withDefault(['nombre' => 'Sistema']);
    }

    /**
     * Scope: Canjes del mes actual
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDelMes($query)
    {
        return $query->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->where('origen', '!=', 'ajuste');
    }

    /**
     * Scope: Canjes de hoy
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('created_at', today())
            ->where('origen', '!=', 'ajuste');
    }

    /**
     * Scope: Canjes entre fechas
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
     * Formatear puntos canjeados
     */
    public function getPuntosFormateadosAttribute(): string
    {
        return number_format($this->puntos_canjeados, 2, ',', '.');
    }

    public function getEsAjusteAttribute(): bool
    {
        return $this->origen === 'ajuste';
    }

    public function getEsAjusteSumaAttribute(): bool
    {
        return $this->es_ajuste && $this->referencia === 'ajuste_suma';
    }

    public function getPuntosFirmadosAttribute(): float
    {
        $valor = (float) $this->puntos_canjeados;

        if ($this->es_ajuste_suma) {
            return $valor;
        }

        return -$valor;
    }

    /**
     * Obtener cupón de canje (código único)
     *
     * @return string
     */
    public function getCodigoCuponAttribute()
    {
        return 'C-'.str_pad($this->id, 8, '0', STR_PAD_LEFT);
    }
}
