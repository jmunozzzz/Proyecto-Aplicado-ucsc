<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultadoEvaluacion extends Model
{
    protected $table = 'resultado_evaluacion';
    public $timestamps = false;

    protected $fillable = [
        'inscripcion_id',
        'instrumento_id',
        'cohorte_id',
        'intento',
        'puntaje',
        'nota',
        'fecha',
        'tipo_evaluador',
        'evaluador_persona_id',
        'momento',
    ];

    protected $casts = [
        'fecha'   => 'datetime',
        'puntaje' => 'decimal:2',
        'nota'    => 'decimal:2',
    ];

    // Relaciones
    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_id');
    }

    public function instrumento(): BelongsTo
    {
        return $this->belongsTo(Instrumento::class, 'instrumento_id');
    }

    public function cohorte(): BelongsTo
    {
        return $this->belongsTo(Cohorte::class, 'cohorte_id');
    }

    public function evaluadorPersona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'evaluador_persona_id');
    }
}
