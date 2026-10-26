<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Empresa extends Model
{
    protected $table = 'empresa';
    public $timestamps = false;

    protected $fillable = [
        'rut',
        'razon_social',
        'nombre_fantasia',
        'rubro_id',
        'tamano',
        'pais',
        'region',
        'ciudad',
        'plan',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    // Relaciones
    public function rubro(): BelongsTo
    {
        return $this->belongsTo(Rubro::class, 'rubro_id');
    }

    public function historialLaboral(): HasMany
    {
        return $this->hasMany(HistorialLaboral::class, 'empresa_id');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'empresa_id');
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class, 'empresa_id');
    }

    public function kpis(): HasMany
    {
        return $this->hasMany(KpiEmpresa::class, 'empresa_id');
    }

    public function reglasAutomaticas(): HasMany
    {
        return $this->hasMany(ReglaAutomatica::class, 'empresa_id');
    }

    public function reportesProgramados(): HasMany
    {
        return $this->hasMany(ReporteProgramado::class, 'empresa_id');
    }

    public function usuarioRoles(): HasMany
    {
        return $this->hasMany(UsuarioRol::class, 'empresa_id');
    }
}
