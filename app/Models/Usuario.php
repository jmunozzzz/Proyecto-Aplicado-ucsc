<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Model
{
    protected $table = 'usuario';
    public $timestamps = false;

    public const PROVEEDOR_LOCAL = 'local';

    protected $fillable = [
        'persona_id',
        'proveedor_auth',
        'sujeto_auth',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relaciones
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(UsuarioRol::class, 'usuario_id');
    }

    public function tieneRol(string $rol): bool
    {
        if (! $this->activo) {
            return false;
        }

        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(
                fn (UsuarioRol $r) => $r->rol === $rol && $r->empresa_id === null
            );
        }

        return $this->roles()
            ->where('rol', $rol)
            ->whereNull('empresa_id')
            ->exists();
    }
}
