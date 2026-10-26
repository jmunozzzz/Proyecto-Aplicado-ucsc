<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instrumento extends Model
{
    protected $table = 'instrumento';
    public $timestamps = false;

    protected $fillable = [
        'cohorte_id',
        'nombre',
        'tipo',
        'competencia_id',
        'escala_min',
        'escala_max',
        'ponderacion_pct',
    ];

    protected $casts = [
        'escala_min'      => 'decimal:2',
        'escala_max'      => 'decimal:2',
        'ponderacion_pct' => 'decimal:2',
    ];

    // Relaciones
    public function cohorte(): BelongsTo
    {
        return $this->belongsTo(Cohorte::class, 'cohorte_id');
    }

    public function competencia(): BelongsTo
    {
        return $this->belongsTo(Competencia::class, 'competencia_id');
    }

    public function resultadosEvaluacion(): HasMany
    {
        return $this->hasMany(ResultadoEvaluacion::class, 'instrumento_id');
    }
}
