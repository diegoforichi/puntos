<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SystemConfig extends Model
{
    use HasFactory;

    protected $table = 'system_config';

    protected $connection = 'mysql';

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener valor de configuración decodificado
     */
    public static function get(string $key, $default = null)
    {
        $config = self::where('key', $key)->first();

        if (! $config) {
            return $default;
        }

        $value = $config->value;

        if (is_string($value)) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Throwable $e) {
                // El valor no estaba encriptado
            }
        }

        $decoded = json_decode($value, true);

        return $decoded ?? $default;
    }

    /**
     * Guardar o actualizar configuración
     */
    public static function set(string $key, $value, ?string $description = null): void
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }

        if ($key === 'email' || $key === 'whatsapp') {
            $value = Crypt::encryptString($value);
        }

        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
            ]
        );
    }

    /**
     * Obtener configuración de WhatsApp
     */
    public static function getWhatsAppConfig(): array
    {
        return self::get('whatsapp', [
            'usar_canal' => true,
            'activo' => false,
            'token' => '',
            'url' => '',
            'codigo_pais' => '+598',
        ]);
    }

    /**
     * Obtener configuración de Email
     */
    public static function getEmailConfig(): array
    {
        return self::get('email', [
            'usar_canal' => true,
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_user' => '',
            'smtp_pass' => '',
            'from_address' => 'sistema@tudominio.com',
            'from_name' => 'Sistema de Puntos',
        ]);
    }

    public static function getSmtpEncryption(): array
    {
        return [
            'ninguno' => null,
            'tls' => 'tls',
            'ssl' => 'ssl',
        ];
    }
}
