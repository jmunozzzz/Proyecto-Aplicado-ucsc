<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaDetalle extends Model
{
    protected $table = 'venta_detalle';
    public $timestamps = false;

    protected $fillable = [
        'venta_id',
        'programa_id',
        'inscripcion_id',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'descuento',
        'total',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'descuento'       => 'decimal:2',
        'total'           => 'decimal:2',
    ];

    // Relaciones
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(Programa::class, 'programa_id');
    }

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_id');
    }
}
