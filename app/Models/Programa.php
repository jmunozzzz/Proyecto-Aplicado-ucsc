<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Programa extends Model
{
    protected $table = 'programa';
    public $timestamps = false;

    /** Tipos de programa ofrecidos en el formulario local ('in_company' se conserva por compatibilidad). */
    public const LINEAS = [
        'abierto'     => 'Abierto',
        'corporativo' => 'Corporativo',
    ];

    public const MODALIDADES = [
        'sincronica'  => 'Sincrónica',
        'asincronica' => 'Asincrónica',
        'mixta'       => 'Mixta',
    ];

    /** Resultado del último intento de sincronizar la estructura. */
    public const ESTRUCTURA_COMPLETA  = 'completa';
    public const ESTRUCTURA_PENDIENTE = 'pendiente_paginacion';
    public const ESTRUCTURA_ERROR     = 'error';

    protected $fillable = [
        'codigo',
        'nombre',
        'version',
        'linea',
        'modalidad',
        'categoria',
        'horas',
        'duracion_descripcion',
        'objetivos',
        'requisitos',
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
        'systeme_activo'           => 'boolean',
        'systeme_presente'         => 'boolean',
        'systeme_creado_en'        => 'datetime',
        'sincronizado_en'          => 'datetime',
        'estructura_intentada_en'  => 'datetime',
        'estructura_confirmada_en' => 'datetime',
    ];

    // Relaciones
    public function modulos(): HasMany
    {
        return $this->hasMany(Modulo::class, 'programa_id')->orderBy('orden');
    }

    public function lecciones(): HasManyThrough
    {
        return $this->hasManyThrough(Leccion::class, Modulo::class, 'programa_id', 'modulo_id');
    }

    /** Hay una estructura completa confirmada (aunque el último intento haya fallado). */
    public function tieneEstructuraConfirmada(): bool
    {
        return $this->estructura_confirmada_en !== null;
    }

    public function lineaLabel(): ?string
    {
        return $this->linea === null ? null : (self::LINEAS[$this->linea] ?? str_replace('_', ' ', ucfirst($this->linea)));
    }

    public function modalidadLabel(): ?string
    {
        return $this->modalidad === null ? null : (self::MODALIDADES[$this->modalidad] ?? $this->modalidad);
    }

    public function cohortes(): HasMany
    {
        return $this->hasMany(Cohorte::class, 'programa_id');
    }

    public function inscripcionesSysteme(): HasMany
    {
        return $this->hasMany(InscripcionSysteme::class, 'programa_id');
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
