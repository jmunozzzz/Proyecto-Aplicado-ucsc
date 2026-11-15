<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioRol extends Model
{
    protected $table = 'usuario_rol';
    public $timestamps = false;

    public const ROL_ADMINISTRADOR = 'administrador_nm';
    public const ROL_RRHH          = 'rrhh';
    public const ROL_FACILITADOR   = 'facilitador';
    public const ROL_ALUMNO        = 'alumno';

    protected $fillable = [
        'usuario_id',
        'rol',
        'empresa_id',
        'empresa_scope',
    ];

    // Relaciones
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
