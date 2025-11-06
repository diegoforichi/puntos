<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campana extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'campanas';

    protected $fillable = [
        'tenant_id',
        'canal',
        'tipo_envio',
        'titulo',
        'subtitulo',
        'imagen_url',
        'asunto_email',
        'cuerpo_texto',
        'mensaje_whatsapp',
        'fecha_programada',
        'estado',
        'totales',
    ];

    protected function casts(): array
    {
        return [
            'fecha_programada' => 'datetime',
            'totales' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function envios()
    {
        return $this->hasMany(CampanaEnvio::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function esProgramada(): bool
    {
        return ! is_null($this->fecha_programada);
    }

    public function puedeEditarse(): bool
    {
        return in_array($this->estado, ['borrador', 'pendiente', 'pausada']);
    }

    public function puedeEliminarse(): bool
    {
        return in_array($this->estado, ['borrador', 'pendiente', 'pausada']);
    }

    public function puedeArchivarse(): bool
    {
        return in_array($this->estado, ['completada', 'cancelada']);
    }

    public function puedePausarse(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function puedeReanudarse(): bool
    {
        return $this->estado === 'pausada';
    }

    public function puedeEnviarse(): bool
    {
        return in_array($this->estado, ['borrador', 'pendiente', 'pausada']);
    }

    public function puedeProbar(): bool
    {
        return in_array($this->estado, ['borrador', 'pendiente', 'pausada']);
    }

    public function obtenerMensajeWhatsApp(): ?string
    {
        if (! in_array($this->canal, ['whatsapp', 'ambos'])) {
            return null;
        }

        return $this->mensaje_whatsapp ?: $this->cuerpo_texto;
    }

    public function construirMensajeWhatsapp(Cliente $cliente, ?Tenant $tenant = null): ?string
    {
        $mensaje = $this->obtenerMensajeWhatsApp();

        if (! $mensaje) {
            return null;
        }

        $tenantNombre = $tenant?->nombre_comercial ?? 'Nuestro comercio';
        $tenantTelefono = $tenant?->telefono_contacto ?? '';
        $tenantEmail = $tenant?->email_contacto ?? '';

        return str_replace([
            '{nombre}',
            '{puntos}',
            '{comercio}',
            '{telefono}',
            '{email}',
            '{documento}',
        ], [
            $cliente->nombre,
            number_format((float) $cliente->puntos_acumulados, 2, ',', '.'),
            $tenantNombre,
            $tenantTelefono,
            $tenantEmail,
            $cliente->documento,
        ], $mensaje);
    }

    public function obtenerContenidoEmail(): array
    {
        if (! in_array($this->canal, ['email', 'ambos'])) {
            return [];
        }

        return [
            'asunto' => $this->asunto_email ?? $this->titulo,
            'titulo' => $this->titulo,
            'subtitulo' => $this->subtitulo,
            'imagen_url' => $this->imagen_url,
            'cuerpo' => $this->cuerpo_texto,
        ];
    }

    public function duplicar(): self
    {
        return self::create([
            'tenant_id' => $this->tenant_id,
            'canal' => $this->canal,
            'tipo_envio' => $this->tipo_envio,
            'titulo' => $this->titulo.' (Copia)',
            'subtitulo' => $this->subtitulo,
            'imagen_url' => $this->imagen_url,
            'asunto_email' => $this->asunto_email,
            'cuerpo_texto' => $this->cuerpo_texto,
            'mensaje_whatsapp' => $this->mensaje_whatsapp,
            'estado' => 'borrador',
            'totales' => json_encode([
                'clientes' => 0,
                'whatsapp' => 0,
                'email' => 0,
                'exitosos' => 0,
                'fallidos' => 0,
            ], JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function canales(): array
    {
        return match ($this->canal) {
            'whatsapp' => ['whatsapp'],
            'email' => ['email'],
            'ambos' => ['whatsapp', 'email'],
            default => [],
        };
    }

    public function incrementarTotales(string $canal, bool $exitoso): void
    {
        // Asegurar que $totales sea un array (puede venir como string JSON desde SQLite)
        $totales = $this->totales;
        if (is_string($totales)) {
            $decoded = json_decode($totales, true);
            $totales = is_array($decoded) ? $decoded : null;
        }

        $totales = $totales ?? [
            'clientes' => 0,
            'whatsapp' => 0,
            'email' => 0,
            'exitosos' => 0,
            'fallidos' => 0,
        ];

        if ($exitoso) {
            $totales['exitosos'] = ($totales['exitosos'] ?? 0) + 1;
        } else {
            $totales['fallidos'] = ($totales['fallidos'] ?? 0) + 1;
        }

        $this->forceFill(['totales' => $totales])->saveQuietly();
    }

    public function marcarCompletadaSiCorresponde(): void
    {
        if ($this->envios()->where('estado', 'pendiente')->count() === 0) {
            $this->update(['estado' => 'completada']);
        }
    }
}
