<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Configuracion
 * 
 * Representa la configuración del tenant
 * Almacena parámetros como puntos_por_pesos, días_vencimiento, etc.
 * 
 * Tabla: configuracion (SQLite del tenant)
 */
class Configuracion extends Model
{
    /**
     * Conexión: usar siempre la base SQLite del tenant
     */
    protected $connection = 'tenant';
    /**
     * Nombre de la tabla
     */
    protected $table = 'configuracion';

    /**
     * Campos asignables en masa
     */
    protected $fillable = [
        'key',
        'value',
        'descripcion',
    ];

    /**
     * Campos que deben ser casteados
     */
    protected $casts = [
        'value' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Claves de configuración disponibles
     */
    const KEY_PUNTOS_POR_PESOS = 'puntos_por_pesos';
    const KEY_DIAS_VENCIMIENTO = 'dias_vencimiento';
    const KEY_CONTACTO = 'contacto';
    const KEY_EVENTOS_WHATSAPP = 'eventos_whatsapp';
    const KEY_ACUMULACION_EXCLUIR_EFACTURAS = 'acumulacion_excluir_efacturas';
    const KEY_MONEDA_BASE = 'moneda_base';
    const KEY_TASA_USD = 'tasa_usd';
    const KEY_MONEDA_DESCONOCIDA = 'moneda_desconocida';

    /**
     * Obtener valor de configuración por clave
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $config = self::where('key', $key)->first();
        
        if (!$config) {
            return $default;
        }

        // Si el value es un array con una sola clave 'valor', devolver solo ese valor
        if (is_array($config->value) && isset($config->value['valor'])) {
            return $config->value['valor'];
        }

        return $config->value;
    }

    /**
     * Establecer valor de configuración
     * 
     * @param string $key
     * @param mixed $value
     * @param string|null $descripcion
     * @return void
     */
    public static function set($key, $value, $descripcion = null)
    {
        if (!is_array($value)) {
            $value = ['valor' => $value];
        }

        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
            ]
        );
    }

    /**
     * Obtener puntos por pesos configurados
     * 
     * @return float
     */
    public static function getPuntosPorPesos()
    {
        return (float) self::get(self::KEY_PUNTOS_POR_PESOS, 100);
    }

    /**
     * Obtener días de vencimiento configurados
     * 
     * @return int
     */
    public static function getDiasVencimiento()
    {
        return (int) self::get(self::KEY_DIAS_VENCIMIENTO, 180);
    }

    /**
     * Obtener datos de contacto del comercio
     * 
     * @return array
     */
    public static function getContacto()
    {
        $contacto = self::get(self::KEY_CONTACTO, []);
        
        // Asegurar que siempre retorne un array con las claves esperadas
        return [
            'nombre_comercial' => $contacto['nombre_comercial'] ?? '',
            'telefono' => $contacto['telefono'] ?? ($contacto['telefono_contacto'] ?? ''),
            'direccion' => $contacto['direccion'] ?? ($contacto['direccion_contacto'] ?? ''),
            'email' => $contacto['email'] ?? ($contacto['email_contacto'] ?? ''),
        ];
    }

    /**
     * Obtener eventos de WhatsApp habilitados
     * 
     * @return array
     */
    public static function getEventosWhatsApp()
    {
        return self::get(self::KEY_EVENTOS_WHATSAPP, [
            'puntos_canjeados' => true,
            'puntos_por_vencer' => true,
            'bienvenida' => false,
            'promociones' => false,
        ]);
    }

    /**
     * Verificar si un evento de WhatsApp está habilitado
     * 
     * @param string $evento
     * @return bool
     */
    public static function eventoWhatsAppHabilitado($evento)
    {
        $eventos = self::getEventosWhatsApp();
        return $eventos[$evento] ?? false;
    }

    /**
     * Obtener descripción por defecto según la clave
     * 
     * @param string $key
     * @return string
     */
    private static function getDescripcionPorDefecto($key)
    {
        return match($key) {
            self::KEY_PUNTOS_POR_PESOS => 'Cantidad de pesos necesarios para generar 1 punto',
            self::KEY_DIAS_VENCIMIENTO => 'Días de validez de los puntos desde la fecha de emisión',
            self::KEY_CONTACTO => 'Datos de contacto del comercio',
            self::KEY_EVENTOS_WHATSAPP => 'Eventos que disparan notificaciones WhatsApp',
            default => 'Configuración del sistema',
        };
    }

    /**
     * Obtener todas las configuraciones como array asociativo
     * 
     * @return array
     */
    public static function todas()
    {
        $configs = self::all();
        $resultado = [];

        foreach ($configs as $config) {
            $resultado[$config->key] = $config->value;
        }

        return $resultado;
    }

    public static function getAcumulacionExcluirEfacturas(): bool
    {
        return (bool) self::get(self::KEY_ACUMULACION_EXCLUIR_EFACTURAS, false);
    }

    public static function getAcumulacion(): array
    {
        return [
            'excluir_efacturas' => (bool) self::get(self::KEY_ACUMULACION_EXCLUIR_EFACTURAS, false),
        ];
    }

    public static function setAcumulacion(bool $excluirEfacturas): void
    {
        self::set(self::KEY_ACUMULACION_EXCLUIR_EFACTURAS, $excluirEfacturas);
    }

    public static function getMonedaBase(): string
    {
        return (string) self::get(self::KEY_MONEDA_BASE, 'UYU');
    }

    public static function getTasaUsd(): float
    {
        return (float) self::get(self::KEY_TASA_USD, 40.0);
    }

    public static function getMonedaDesconocida(): string
    {
        $valor = self::get(self::KEY_MONEDA_DESCONOCIDA, 'omitir');
        return in_array($valor, ['omitir', 'sin_convertir'], true) ? $valor : 'omitir';
    }

    public static function setMonedaConfig(string $monedaBase, float $tasaUsd, string $monedaDesconocida): void
    {
        self::set(self::KEY_MONEDA_BASE, strtoupper(trim($monedaBase)) ?: 'UYU');
        self::set(self::KEY_TASA_USD, $tasaUsd);
        $modo = in_array($monedaDesconocida, ['omitir', 'sin_convertir'], true) ? $monedaDesconocida : 'omitir';
        self::set(self::KEY_MONEDA_DESCONOCIDA, $modo);
    }
}
