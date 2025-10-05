<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Factura
 * 
 * Representa una factura de referencia que generó puntos
 * Las facturas tienen fecha de vencimiento para los puntos
 * 
 * Tabla: facturas (SQLite del tenant)
 */
class Factura extends Model
{
    /**
     * Nombre de la tabla
     */
    protected $table = 'facturas';

    /**
     * Campos asignables en masa
     */
    protected $fillable = [
        'cliente_id',
        'numero_factura',
        'monto_total',
        'moneda',
        'puntos_generados',
        'promocion_aplicada',
        'payload_json',
        'fecha_emision',
        'fecha_vencimiento',
    ];

    /**
     * Campos que deben ser casteados
     */
    protected $casts = [
        'cliente_id' => 'integer',
        'monto_total' => 'decimal:2',
        'puntos_generados' => 'decimal:2',
        'payload_json' => 'array',
        'fecha_emision' => 'datetime',
        'fecha_vencimiento' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación: Factura pertenece a un cliente
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Scope: Facturas activas (no vencidas)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivas($query)
    {
        return $query->where('fecha_vencimiento', '>=', now());
    }

    /**
     * Scope: Facturas vencidas
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVencidas($query)
    {
        return $query->where('fecha_vencimiento', '<', now());
    }

    /**
     * Scope: Facturas por vencer en los próximos N días
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $dias
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorVencer($query, $dias = 30)
    {
        return $query->whereBetween('fecha_vencimiento', [
            now(),
            now()->addDays($dias)
        ]);
    }

    /**
     * Scope: Facturas del mes actual
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDelMes($query)
    {
        return $query->whereYear('fecha_emision', date('Y'))
            ->whereMonth('fecha_emision', date('m'));
    }

    /**
     * Scope: Facturas con promoción aplicada
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConPromocion($query)
    {
        return $query->whereNotNull('promocion_aplicada');
    }

    /**
     * Verificar si la factura está vencida
     * 
     * @return bool
     */
    public function estaVencida()
    {
        return $this->fecha_vencimiento < now();
    }

    /**
     * Obtener días restantes para vencimiento
     * 
     * @return int
     */
    public function diasParaVencer()
    {
        if ($this->estaVencida()) {
            return 0;
        }

        return now()->diffInDays($this->fecha_vencimiento);
    }

    /**
     * Formatear monto con moneda
     * 
     * @return string
     */
    public function getMontoFormateadoAttribute()
    {
        $simbolo = match($this->moneda) {
            'USD' => 'U$S',
            'UYU' => '$',
            'EUR' => '€',
            default => $this->moneda,
        };

        return $simbolo . ' ' . number_format($this->monto_total, 2, ',', '.');
    }

    /**
     * Formatear puntos generados
     * 
     * @return string
     */
    public function getPuntosFormateadosAttribute()
    {
        return number_format($this->puntos_generados, 2, ',', '.');
    }

    /**
     * Obtener badge de estado (activa/vencida/por vencer)
     * 
     * @return array [class, text]
     */
    public function getBadgeEstadoAttribute()
    {
        if ($this->estaVencida()) {
            return ['class' => 'bg-danger', 'text' => 'Vencida'];
        }

        $diasRestantes = $this->diasParaVencer();

        if ($diasRestantes <= 7) {
            return ['class' => 'bg-danger', 'text' => "Vence en {$diasRestantes} días"];
        }

        if ($diasRestantes <= 30) {
            return ['class' => 'bg-warning', 'text' => "Vence en {$diasRestantes} días"];
        }

        return ['class' => 'bg-success', 'text' => 'Activa'];
    }
}
