<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo PuntosVencido
 * 
 * Representa puntos que han vencido
 * Se registran cuando las facturas expiran
 * 
 * Tabla: puntos_vencidos (SQLite del tenant)
 */
class PuntosVencido extends Model
{
    /**
     * Nombre de la tabla
     */
    protected $table = 'puntos_vencidos';

    /**
     * Campos asignables en masa
     */
    protected $fillable = [
        'cliente_id',
        'puntos_vencidos',
        'motivo',
    ];

    /**
     * Campos que deben ser casteados
     */
    protected $casts = [
        'cliente_id' => 'integer',
        'puntos_vencidos' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación: Vencimiento pertenece a un cliente
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Scope: Vencimientos del mes actual
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDelMes($query)
    {
        return $query->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'));
    }

    /**
     * Scope: Vencimientos entre fechas
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $desde
     * @param string $hasta
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('created_at', [$desde, $hasta]);
    }

    /**
     * Formatear puntos vencidos
     * 
     * @return string
     */
    public function getPuntosFormateadosAttribute()
    {
        return number_format($this->puntos_vencidos, 2, ',', '.');
    }
}
