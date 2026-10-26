<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Vista SQL: v_historial_formacion
 * Solo lectura — vista desnormalizada con historial completo de formación.
 */
class VHistorialFormacion extends Model
{
    protected $table = 'v_historial_formacion';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'inscripcion_id';

    protected $guarded = ['*'];

    protected $casts = [
        'fecha_inicio'    => 'date',
        'fecha_fin'       => 'date',
        'avance_pct'      => 'decimal:2',
        'asistencia_pct'  => 'decimal:2',
        'nota_final'      => 'decimal:2',
        'plazo'           => 'date',
        'ultimo_ingreso'  => 'datetime',
        'ultimo_avance_en' => 'datetime',
    ];
}
