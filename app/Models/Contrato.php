<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contrato extends Model
{
    protected $table = 'contrato';
    public $timestamps = false;

    protected $fillable = [
        'empresa_id',
        'fecha_inicio',
        'fecha_fin',
        'monto',
        'moneda',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'monto'        => 'decimal:2',
    ];

    // Relaciones
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'contrato_id');
    }
}
