<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampanaEnvio extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'campana_envios';

    protected $fillable = [
        'campana_id',
        'cliente_id',
        'canal',
        'estado',
        'intentos',
        'error_mensaje',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function campana()
    {
        return $this->belongsTo(Campana::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function marcarEnviado(): void
    {
        $this->update([
            'estado' => 'enviado',
            'intentos' => $this->intentos + 1,
            'sent_at' => now(),
        ]);
    }

    public function registrarFallo(string $mensaje): void
    {
        $this->update([
            'estado' => 'fallido',
            'intentos' => $this->intentos + 1,
            'error_mensaje' => $mensaje,
        ]);
    }
}
