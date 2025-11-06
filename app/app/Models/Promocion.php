<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Promocion
 *
 * Representa una campaña de promoción de puntos
 * Tipos: multiplicador, puntos_extra, descuento_canje
 *
 * Tabla: promociones (SQLite del tenant)
 */
class Promocion extends Model
{
    /**
     * Nombre de la tabla
     */
    protected $table = 'promociones';

    /**
     * Conexión a la base de datos del tenant
     */
    protected $connection = 'tenant';

    /**
     * Campos asignables en masa
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'valor',
        'condiciones',
        'fecha_inicio',
        'fecha_fin',
        'prioridad',
        'activa',
    ];

    /**
     * Campos que deben ser casteados
     */
    protected $casts = [
        'valor' => 'decimal:2',
        'condiciones' => 'array',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'prioridad' => 'integer',
        'activa' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Tipos de promoción disponibles
     */
    const TIPO_BONIFICACION = 'bonificacion';

    const TIPO_MULTIPLICADOR = 'multiplicador';

    /**
     * Scope: Promociones activas
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', 1)
            ->where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now());
    }

    /**
     * Scope: Promociones vencidas
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVencidas($query)
    {
        return $query->where('fecha_fin', '<', now());
    }

    /**
     * Scope: Promociones programadas (futuras)
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProgramadas($query)
    {
        return $query->where('fecha_inicio', '>', now());
    }

    /**
     * Scope: Filtrar por tipo
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $tipo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Verificar si la promoción está vigente
     *
     * @return bool
     */
    public function estaVigente()
    {
        return $this->activa
            && $this->fecha_inicio <= now()
            && $this->fecha_fin >= now();
    }

    /**
     * Aplicar promoción a un monto de factura (método de instancia)
     *
     * @param  float  $monto
     * @param  float  $puntosBase
     * @return float
     */
    public function aplicarPromocion($monto, $puntosBase)
    {
        if (! $this->estaVigente()) {
            return $puntosBase;
        }

        // Verificar condiciones
        if (! $this->cumpleCondiciones($monto)) {
            return $puntosBase;
        }

        return match ($this->tipo) {
            self::TIPO_BONIFICACION => $puntosBase * (1 + ($this->valor / 100)), // % extra
            self::TIPO_MULTIPLICADOR => $puntosBase * $this->valor, // Factor multiplicador
            default => $puntosBase,
        };
    }

    /**
     * Verificar si se cumplen las condiciones de la promoción
     *
     * @param  float  $monto
     * @return bool
     */
    public function cumpleCondiciones($monto)
    {
        if (empty($this->condiciones)) {
            return true;
        }

        $condicion = $this->condiciones;

        // Verificar monto mínimo
        if (isset($condicion['monto_minimo'])) {
            if ($monto < $condicion['monto_minimo']) {
                return false;
            }
        }

        // Verificar monto máximo
        if (isset($condicion['monto_maximo'])) {
            if ($monto > $condicion['monto_maximo']) {
                return false;
            }
        }

        // Verificar día de la semana
        if (isset($condicion['dias_semana'])) {
            $diaActual = now()->dayOfWeek; // 0=Domingo, 6=Sábado
            if (! in_array($diaActual, $condicion['dias_semana'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtener badge de estado
     *
     * @return array [class, text]
     */
    public function getBadgeEstadoAttribute()
    {
        if (! $this->activa) {
            return ['class' => 'bg-secondary', 'text' => 'Inactiva'];
        }

        if ($this->fecha_inicio > now()) {
            return ['class' => 'bg-info', 'text' => 'Programada'];
        }

        if ($this->fecha_fin < now()) {
            return ['class' => 'bg-danger', 'text' => 'Vencida'];
        }

        return ['class' => 'bg-success', 'text' => 'Activa'];
    }

    /**
     * Obtener nombre del tipo de promoción
     *
     * @return string
     */
    public function getTipoNombreAttribute()
    {
        return match ($this->tipo) {
            self::TIPO_BONIFICACION => 'Bonificación',
            self::TIPO_MULTIPLICADOR => 'Multiplicador',
            default => 'Desconocido',
        };
    }

    /**
     * Obtener descripción del valor según el tipo
     *
     * @return string
     */
    public function getValorDescripcionAttribute()
    {
        return match ($this->tipo) {
            self::TIPO_BONIFICACION => "+{$this->valor}% puntos",
            self::TIPO_MULTIPLICADOR => "x{$this->valor}",
            default => $this->valor,
        };
    }

    /**
     * Obtener array con todos los tipos disponibles
     *
     * @return array
     */
    public static function tiposDisponibles()
    {
        return [
            self::TIPO_BONIFICACION => 'Bonificación de puntos (% extra)',
            self::TIPO_MULTIPLICADOR => 'Multiplicador de puntos (2x, 3x)',
        ];
    }

    /**
     * Aplicar promoción automáticamente a una factura (método estático)
     *
     * Busca la mejor promoción activa y aplicable,
     * priorizando por mayor prioridad y mayor beneficio
     *
     * @param  float  $monto  Monto total de la factura
     * @param  float  $puntosBase  Puntos calculados sin promoción
     * @param  \DateTime  $fecha  Fecha de la factura
     * @param  int|null  $clienteId  ID del cliente (para futuras condiciones)
     * @return array ['promocion_id' => int|null, 'puntos_finales' => float]
     */
    public static function aplicar(float $monto, float $puntosBase, $fecha = null, $clienteId = null): array
    {
        // Buscar promociones activas ordenadas por prioridad
        $promociones = self::activas()
            ->orderBy('prioridad', 'desc')
            ->get();

        if ($promociones->isEmpty()) {
            return [
                'promocion_id' => null,
                'puntos_finales' => $puntosBase,
            ];
        }

        // Intentar aplicar cada promoción (la primera que aplique gana)
        foreach ($promociones as $promocion) {
            if ($promocion->cumpleCondiciones($monto)) {
                $puntosConPromocion = $promocion->aplicarPromocion($monto, $puntosBase);

                return [
                    'promocion_id' => $promocion->id,
                    'puntos_finales' => $puntosConPromocion,
                ];
            }
        }

        // Si ninguna aplica, retornar puntos base
        return [
            'promocion_id' => null,
            'puntos_finales' => $puntosBase,
        ];
    }
}
