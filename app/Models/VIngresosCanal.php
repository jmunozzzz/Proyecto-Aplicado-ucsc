<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Vista SQL: v_ingresos_canal
 * Solo lectura — ingresos agrupados por canal, moneda y mes.
 */
class VIngresosCanalModel extends Model
{
    protected $table = 'v_ingresos_canal';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $guarded = ['*'];

    protected $casts = [
        'cobros_aprobados' => 'decimal:2',
        'comisiones'       => 'decimal:2',
        'neto_comisiones'  => 'decimal:2',
    ];
}
