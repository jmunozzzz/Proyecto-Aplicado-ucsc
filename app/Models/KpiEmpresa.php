<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiEmpresa extends Model
{
    protected $table = 'kpi_empresa';
    public $timestamps = false;

    protected $fillable = [
        'empresa_id',
        'cohorte_id',
        'indicador',
        'unidad',
        'valor_antes',
        'fecha_antes',
        'valor_despues',
        'fecha_despues',
        'beneficio_monetario',
        'costo_intervencion',
        'moneda',
        'metodologia',
    ];

    protected $casts = [
        'fecha_antes'         => 'date',
        'fecha_despues'       => 'date',
        'valor_antes'         => 'decimal:4',
        'valor_despues'       => 'decimal:4',
        'beneficio_monetario' => 'decimal:2',
        'costo_intervencion'  => 'decimal:2',
    ];

    // Relaciones
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function cohorte(): BelongsTo
    {
        return $this->belongsTo(Cohorte::class, 'cohorte_id');
    }
}
