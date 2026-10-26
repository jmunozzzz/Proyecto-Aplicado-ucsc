<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudDatos extends Model
{
    protected $table = 'solicitud_datos';
    public $timestamps = false;

    protected $fillable = [
        'persona_id',
        'tipo',
        'fecha',
        'detalle',
        'estado',
        'respuesta',
        'respondida_en',
        'responsable_id',
    ];

    protected $casts = [
        'fecha'         => 'datetime',
        'respondida_en' => 'datetime',
    ];

    // Relaciones
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'responsable_id');
    }
}
