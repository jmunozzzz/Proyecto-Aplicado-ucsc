<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObjetivoPdi extends Model
{
    protected $table = 'objetivo_pdi';
    public $timestamps = false;

    protected $fillable = [
        'persona_id',
        'empresa_id',
        'competencia_id',
        'objetivo',
        'indicador',
        'meta',
        'unidad',
        'avance_pct',
        'fecha_inicio',
        'fecha_limite',
        'validado_por_id',
        'validado_en',
    ];

    protected $casts = [
        'fecha_inicio'  => 'date',
        'fecha_limite'  => 'date',
        'validado_en'   => 'datetime',
        'meta'          => 'decimal:4',
        'avance_pct'    => 'decimal:2',
    ];

    // Relaciones
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function competencia(): BelongsTo
    {
        return $this->belongsTo(Competencia::class, 'competencia_id');
    }

    public function validadoPor(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'validado_por_id');
    }
}
