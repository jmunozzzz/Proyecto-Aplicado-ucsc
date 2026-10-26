<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modulo extends Model
{
    protected $table = 'modulo';
    public $timestamps = false;

    protected $fillable = [
        'programa_id',
        'titulo',
        'orden',
        'id_systeme_modulo',
    ];

    // Relaciones
    public function programa(): BelongsTo
    {
        return $this->belongsTo(Programa::class, 'programa_id');
    }

    public function lecciones(): HasMany
    {
        return $this->hasMany(Leccion::class, 'modulo_id');
    }
}
