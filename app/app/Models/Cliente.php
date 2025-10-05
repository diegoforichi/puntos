<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Cliente
 * 
 * Representa un cliente final del comercio (tenant)
 * Cada cliente acumula puntos por sus compras
 * 
 * Tabla: clientes (SQLite del tenant)
 */
class Cliente extends Model
{
    /**
     * Nombre de la tabla
     */
    protected $table = 'clientes';

    /**
     * Campos asignables en masa
     */
    protected $fillable = [
        'documento',
        'nombre',
        'telefono',
        'email',
        'direccion',
        'puntos_acumulados',
        'ultima_actividad',
    ];

    /**
     * Campos que deben ser casteados
     */
    protected $casts = [
        'puntos_acumulados' => 'decimal:2',
        'ultima_actividad' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación: Cliente tiene muchas facturas
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function facturas()
    {
        return $this->hasMany(Factura::class, 'cliente_id');
    }

    /**
     * Relación: Cliente tiene muchos canjes de puntos
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function puntosCanjeados()
    {
        return $this->hasMany(PuntosCanjeado::class, 'cliente_id');
    }

    /**
     * Relación: Cliente tiene muchos puntos vencidos
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function puntosVencidos()
    {
        return $this->hasMany(PuntosVencido::class, 'cliente_id');
    }

    /**
     * Obtener facturas activas (no canjeadas ni vencidas)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function facturasActivas()
    {
        return $this->hasMany(Factura::class, 'cliente_id')
            ->where('fecha_vencimiento', '>=', now());
    }

    /**
     * Obtener facturas por vencer en los próximos N días
     * 
     * @param int $dias
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function facturasPorVencer($dias = 30)
    {
        return $this->hasMany(Factura::class, 'cliente_id')
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays($dias)]);
    }

    /**
     * Scope: Clientes activos (con actividad reciente)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $dias
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivos($query, $dias = 30)
    {
        return $query->where('ultima_actividad', '>=', now()->subDays($dias));
    }

    /**
     * Scope: Clientes con puntos disponibles
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConPuntos($query)
    {
        return $query->where('puntos_acumulados', '>', 0);
    }

    /**
     * Scope: Buscar por documento o nombre
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBuscar($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('documento', 'LIKE', "%{$search}%")
              ->orWhere('nombre', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Verificar si el cliente tiene puntos suficientes
     * 
     * @param float $puntos
     * @return bool
     */
    public function tienePuntosSuficientes($puntos)
    {
        return $this->puntos_acumulados >= $puntos;
    }

    /**
     * Formatear teléfono para WhatsApp (código país Uruguay)
     * 
     * @return string|null
     */
    public function getTelefonoWhatsappAttribute()
    {
        if (!$this->telefono) {
            return null;
        }

        // Si ya tiene código país, devolver tal cual
        if (str_starts_with($this->telefono, '+')) {
            return $this->telefono;
        }

        // Agregar código de Uruguay (+598) y remover el 0 inicial
        $telefono = ltrim($this->telefono, '0');
        return '+598' . $telefono;
    }

    /**
     * Obtener iniciales del nombre
     * 
     * @return string
     */
    public function getInicialesAttribute()
    {
        $palabras = explode(' ', $this->nombre);
        $iniciales = '';
        
        foreach (array_slice($palabras, 0, 2) as $palabra) {
            $iniciales .= strtoupper(substr($palabra, 0, 1));
        }
        
        return $iniciales;
    }

    /**
     * Formatear puntos con separadores
     * 
     * @return string
     */
    public function getPuntosFormateadosAttribute()
    {
        return number_format($this->puntos_acumulados, 2, ',', '.');
    }
}
