<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Persona extends Model
{
    protected $table = 'persona';
    public $timestamps = false;

    protected $fillable = [
        'nombres',
        'apellidos',
        'tipo_documento',
        'numero_documento',
        'pais_documento',
        'email',
        'telefono',
        'pais',
        'region',
        'ciudad',
        'fecha_nacimiento',
        'genero',
        'nivel_educacional',
        'linkedin',
        'origen_lead',
        'estado',
        'id_systeme_contacto',
        'creado_en',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'creado_en'        => 'datetime',
    ];

    // Nombre completo como atributo calculado
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    // Relaciones
    public function usuario(): HasOne
    {
        return $this->hasOne(Usuario::class, 'persona_id');
    }

    public function historialLaboral(): HasMany
    {
        return $this->hasMany(HistorialLaboral::class, 'persona_id');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'persona_id');
    }

    public function interaccionesMarketing(): HasMany
    {
        return $this->hasMany(InteraccionMarketing::class, 'persona_id');
    }

    public function solicitudesDatos(): HasMany
    {
        return $this->hasMany(SolicitudDatos::class, 'persona_id');
    }
}
