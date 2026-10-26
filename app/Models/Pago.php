<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pago';
    public $timestamps = false;

    protected $fillable = [
        'venta_id',
        'monto',
        'moneda',
        'medio',
        'proveedor',
        'id_transaccion',
        'estado',
        'fecha',
        'comision',
    ];

    protected $casts = [
        'monto'    => 'decimal:2',
        'comision' => 'decimal:2',
        'fecha'    => 'datetime',
    ];

    // Relaciones
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}
