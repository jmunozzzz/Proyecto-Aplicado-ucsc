<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sesion extends Model
{
    protected $table = 'sesion';
    public $timestamps = false;

    protected $fillable = [
        'cohorte_id',
        'facilitador_id',
        'inicio',
        'fin',
        'tema',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'fin'    => 'datetime',
    ];

    // Relaciones
    public function cohorte(): BelongsTo
    {
        return $this->belongsTo(Cohorte::class, 'cohorte_id');
    }

    public function facilitador(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'facilitador_id');
    }
}
