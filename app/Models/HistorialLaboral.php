<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistorialLaboral extends Model
{
    protected $table = 'historial_laboral';
    public $timestamps = false;

    protected $fillable = [
        'persona_id',
        'empresa_id',
        'cargo',
        'area',
        'nivel_jerarquico',
        'personas_a_cargo',
        'jefe_directo_id',
        'centro_costo',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    // Relaciones
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function jefDirecto(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'jefe_directo_id');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'historial_laboral_id');
    }
}
