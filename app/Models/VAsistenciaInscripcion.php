<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vista SQL: v_asistencia_inscripcion
 * Solo lectura — no tiene PK real, usar inscripcion_id como clave.
 */
class VAsistenciaInscripcion extends Model
{
    protected $table = 'v_asistencia_inscripcion';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'inscripcion_id';

    // Sin fillable — es una vista, solo lectura
    protected $guarded = ['*'];

    protected $casts = [
        'asistencia_pct'     => 'decimal:2',
        'minutos_conectados' => 'decimal:0',
    ];

    // Relaciones
    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_id');
    }
}
