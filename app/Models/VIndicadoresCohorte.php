<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vista SQL: v_indicadores_cohorte
 * Solo lectura — usar cohorte_id como clave.
 */
class VIndicadoresCohorte extends Model
{
    protected $table = 'v_indicadores_cohorte';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'cohorte_id';

    protected $guarded = ['*'];

    protected $casts = [
        'finalizacion_pct' => 'decimal:2',
        'abandono_pct'     => 'decimal:2',
    ];

    // Relaciones
    public function cohorte(): BelongsTo
    {
        return $this->belongsTo(Cohorte::class, 'cohorte_id');
    }
}
