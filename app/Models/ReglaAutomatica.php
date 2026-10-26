<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReglaAutomatica extends Model
{
    protected $table = 'regla_automatica';
    public $timestamps = false;

    protected $fillable = [
        'empresa_id',
        'nombre',
        'condicion',
        'accion',
        'activa',
        'version',
    ];

    protected $casts = [
        'condicion' => 'array',
        'accion'    => 'array',
        'activa'    => 'boolean',
    ];

    // Relaciones
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
