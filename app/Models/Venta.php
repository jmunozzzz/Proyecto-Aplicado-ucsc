<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $table = 'venta';
    public $timestamps = false;

    protected $fillable = [
        'persona_pagadora_id',
        'empresa_pagadora_id',
        'contrato_id',
        'vendedor_id',
        'fecha',
        'moneda',
        'canal',
        'campana',
        'cupon',
        'id_systeme_pedido',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    // Relaciones
    public function personaPagadora(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_pagadora_id');
    }

    public function empresaPagadora(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_pagadora_id');
    }

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'vendedor_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class, 'venta_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'venta_id');
    }
}
