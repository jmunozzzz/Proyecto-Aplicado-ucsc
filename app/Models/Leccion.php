<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Leccion extends Model
{
    protected $table = 'leccion';
    public $timestamps = false;

    protected $fillable = [
        'modulo_id',
        'titulo',
        'orden',
        'duracion_min',
        'competencia_id',
        'id_systeme_leccion',
    ];

    protected $casts = [
        'orden'            => 'integer',
        // Estado de publicación de la lección en Systeme.io. No representa progreso,
        // pago ni acceso de ningún alumno.
        'systeme_activo'   => 'boolean',
        'systeme_presente' => 'boolean',
    ];

    // Relaciones
    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'modulo_id');
    }

    public function competencia(): BelongsTo
    {
        return $this->belongsTo(Competencia::class, 'competencia_id');
    }
}
