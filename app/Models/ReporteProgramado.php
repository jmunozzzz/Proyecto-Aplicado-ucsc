<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteProgramado extends Model
{
    protected $table = 'reporte_programado';
    public $timestamps = false;

    protected $fillable = [
        'empresa_id',
        'nombre',
        'tipo',
        'frecuencia',
        'zona_horaria',
        'proxima_ejecucion',
        'activo',
    ];

    protected $casts = [
        'proxima_ejecucion' => 'datetime',
        'activo'            => 'boolean',
    ];

    // Relaciones
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
