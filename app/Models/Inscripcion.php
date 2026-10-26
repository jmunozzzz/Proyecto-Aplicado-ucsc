<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Inscripcion extends Model
{
    protected $table = 'inscripcion';
    public $timestamps = false;

    protected $fillable = [
        'persona_id',
        'cohorte_id',
        'empresa_id',
        'historial_laboral_id',
        'fecha',
        'via',
        'financiamiento',
        'estado',
        'avance_pct',
        'nota_final',
        'fecha_termino',
        'prioridad',
        'plazo',
        'tiempo_dedicado_min',
        'ultimo_ingreso',
        'ultimo_avance_en',
        'id_systeme_inscripcion',
        'consentimiento_id',
    ];

    protected $casts = [
        'fecha'           => 'datetime',
        'fecha_termino'   => 'date',
        'plazo'           => 'date',
        'ultimo_ingreso'  => 'datetime',
        'ultimo_avance_en' => 'datetime',
        'avance_pct'      => 'decimal:2',
        'nota_final'      => 'decimal:2',
    ];

    // Relaciones
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function cohorte(): BelongsTo
    {
        return $this->belongsTo(Cohorte::class, 'cohorte_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function historialLaboral(): BelongsTo
    {
        return $this->belongsTo(HistorialLaboral::class, 'historial_laboral_id');
    }

    public function resultadosEvaluacion(): HasMany
    {
        return $this->hasMany(ResultadoEvaluacion::class, 'inscripcion_id');
    }

    public function satisfacciones(): HasMany
    {
        return $this->hasMany(Satisfaccion::class, 'inscripcion_id');
    }

    public function ventaDetalle(): HasOne
    {
        return $this->hasOne(VentaDetalle::class, 'inscripcion_id');
    }

    // Vista: asistencia
    public function asistencia(): HasOne
    {
        return $this->hasOne(VAsistenciaInscripcion::class, 'inscripcion_id');
    }
}
