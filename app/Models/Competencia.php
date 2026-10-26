<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Competencia extends Model
{
    protected $table = 'competencia';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria',
    ];

    // Relaciones
    public function programas(): BelongsToMany
    {
        return $this->belongsToMany(
            Programa::class,
            'programa_competencia',
            'competencia_id',
            'programa_id'
        );
    }

    public function objetivosPdi(): HasMany
    {
        return $this->hasMany(ObjetivoPdi::class, 'competencia_id');
    }

    public function instrumentos(): HasMany
    {
        return $this->hasMany(Instrumento::class, 'competencia_id');
    }

    public function lecciones(): HasMany
    {
        return $this->hasMany(Leccion::class, 'competencia_id');
    }
}
