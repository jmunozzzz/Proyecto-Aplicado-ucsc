<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Satisfaccion extends Model
{
    protected $table = 'satisfaccion';
    public $timestamps = false;

    protected $fillable = [
        'inscripcion_id',
        'cohorte_id',
        'facilitador_id',
        'fecha',
        'nota_programa',
        'nota_facilitador',
        'nps',
        'aplicabilidad',
        'comentario',
    ];

    protected $casts = [
        'fecha'            => 'datetime',
        'nota_programa'    => 'decimal:2',
        'nota_facilitador' => 'decimal:2',
    ];

    // Relaciones
    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_id');
    }

    public function cohorte(): BelongsTo
    {
        return $this->belongsTo(Cohorte::class, 'cohorte_id');
    }

    public function facilitador(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'facilitador_id');
    }
}
