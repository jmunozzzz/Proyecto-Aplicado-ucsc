<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Cohorte extends Model
{
    protected $table = 'cohorte';
    public $timestamps = false;

    protected $fillable = [
        'programa_id',
        'codigo',
        'facilitador_id',
        'fecha_inicio',
        'fecha_fin',
        'modalidad',
        'plataforma',
        'cupo_max',
        'estado',
        'id_systeme_cohorte',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    // Relaciones
    public function programa(): BelongsTo
    {
        return $this->belongsTo(Programa::class, 'programa_id');
    }

    public function facilitador(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'facilitador_id');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'cohorte_id');
    }

    public function modulos(): HasMany
    {
        return $this->hasMany(Modulo::class, 'programa_id', 'programa_id');
    }

    public function sesiones(): HasMany
    {
        return $this->hasMany(Sesion::class, 'cohorte_id');
    }

    public function resultadosEvaluacion(): HasMany
    {
        return $this->hasMany(ResultadoEvaluacion::class, 'cohorte_id');
    }

    public function satisfacciones(): HasMany
    {
        return $this->hasMany(Satisfaccion::class, 'cohorte_id');
    }

    public function kpis(): HasMany
    {
        return $this->hasMany(KpiEmpresa::class, 'cohorte_id');
    }

    // Vista: indicadores de cohorte
    public function indicadores()
    {
        return $this->hasOne(VIndicadoresCohorte::class, 'cohorte_id');
    }
}
