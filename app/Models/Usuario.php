<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Model
{
    protected $table = 'usuario';
    public $timestamps = false;

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
}
