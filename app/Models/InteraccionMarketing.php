<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InteraccionMarketing extends Model
{
    protected $table = 'interaccion_marketing';
    public $timestamps = false;

    protected $fillable = [
        'persona_id',
        'fuente',
        'campana',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'evento',
        'etapa_embudo',
        'puntaje_lead',
        'fecha',
        'id_systeme_evento',
    ];

    protected $casts = [
        'fecha'        => 'datetime',
        'puntaje_lead' => 'decimal:2',
    ];

    // Relaciones
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }
}
