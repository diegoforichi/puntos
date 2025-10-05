<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Usuario
 * 
 * Representa un usuario del sistema del tenant
 * Roles: admin, supervisor, operario
 * 
 * Tabla: usuarios (SQLite del tenant)
 */
class Usuario extends Model
{
    /**
     * Nombre de la tabla
     */
    protected $table = 'usuarios';

    /**
     * Campos asignables en masa
     */
    protected $fillable = [
        'nombre',
        'email',
        'username',
        'password',
        'rol',
        'activo',
        'ultimo_acceso',
    ];

    /**
     * Campos ocultos (no se incluyen en JSON)
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Campos que deben ser casteados
     */
    protected $casts = [
        'activo' => 'boolean',
        'ultimo_acceso' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Roles disponibles
     */
    const ROL_ADMIN = 'admin';
    const ROL_SUPERVISOR = 'supervisor';
    const ROL_OPERARIO = 'operario';

    /**
     * Relación: Usuario tiene muchas actividades
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'usuario_id');
    }

    /**
     * Scope: Usuarios activos
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', 1);
    }

    /**
     * Scope: Usuarios inactivos
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactivos($query)
    {
        return $query->where('activo', 0);
    }

    /**
     * Scope: Filtrar por rol
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $rol
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConRol($query, $rol)
    {
        return $query->where('rol', $rol);
    }

    /**
     * Verificar si el usuario es admin
     * 
     * @return bool
     */
    public function esAdmin()
    {
        return $this->rol === self::ROL_ADMIN;
    }

    /**
     * Alias de esAdmin() para compatibilidad
     * 
     * @return bool
     */
    public function isAdmin()
    {
        return $this->esAdmin();
    }

    /**
     * Verificar si el usuario es supervisor
     * 
     * @return bool
     */
    public function esSupervisor()
    {
        return $this->rol === self::ROL_SUPERVISOR;
    }

    /**
     * Alias de esSupervisor() para compatibilidad
     * 
     * @return bool
     */
    public function isSupervisor()
    {
        return $this->esSupervisor();
    }

    /**
     * Verificar si el usuario es operario
     * 
     * @return bool
     */
    public function esOperario()
    {
        return $this->rol === self::ROL_OPERARIO;
    }

    /**
     * Alias de esOperario() para compatibilidad
     * 
     * @return bool
     */
    public function isOperario()
    {
        return $this->esOperario();
    }

    /**
     * Verificar si el usuario puede canjear puntos
     * 
     * @return bool
     */
    public function puedeCanjearPuntos()
    {
        return in_array($this->rol, [self::ROL_ADMIN, self::ROL_SUPERVISOR]);
    }

    /**
     * Verificar si el usuario puede modificar configuración
     * 
     * @return bool
     */
    public function puedeModificarConfiguracion()
    {
        return in_array($this->rol, [self::ROL_ADMIN, self::ROL_SUPERVISOR]);
    }

    /**
     * Obtener color del badge según el rol
     * 
     * @return string
     */
    public function getBadgeColorAttribute()
    {
        return match($this->rol) {
            self::ROL_ADMIN => 'badge-role-admin',
            self::ROL_SUPERVISOR => 'badge-role-supervisor',
            self::ROL_OPERARIO => 'badge-role-operario',
            default => 'bg-secondary',
        };
    }

    /**
     * Obtener nombre del rol en español
     * 
     * @return string
     */
    public function getRolNombreAttribute()
    {
        return match($this->rol) {
            self::ROL_ADMIN => 'Administrador',
            self::ROL_SUPERVISOR => 'Supervisor',
            self::ROL_OPERARIO => 'Operario',
            default => 'Usuario',
        };
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
     * Obtener array con todos los roles disponibles
     * 
     * @return array
     */
    public static function rolesDisponibles()
    {
        return [
            self::ROL_ADMIN => 'Administrador',
            self::ROL_SUPERVISOR => 'Supervisor',
            self::ROL_OPERARIO => 'Operario',
        ];
    }
}
