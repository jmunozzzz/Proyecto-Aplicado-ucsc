<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Programa extends Model
{
    protected $table = 'programa';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'version',
        'linea',
        'modalidad',
        'horas',
        'duracion_descripcion',
        'precio_lista',
        'moneda',
        'codigo_sence',
        'id_systeme_curso',
        'activo',
    ];

    protected $casts = [
        'horas'       => 'decimal:2',
        'precio_lista' => 'decimal:2',
        'activo'      => 'boolean',
    ];

    // Relaciones
    public function cohortes(): HasMany
    {
        return $this->hasMany(Cohorte::class, 'programa_id');
    }

    public function competencias(): BelongsToMany
    {
        return $this->belongsToMany(
            Competencia::class,
            'programa_competencia',
            'programa_id',
            'competencia_id'
        );
    }

    public function ventaDetalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class, 'programa_id');
    }
}
