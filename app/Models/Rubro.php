<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rubro extends Model
{
    protected $table = 'rubro';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    // Relaciones
    public function empresas(): HasMany
    {
        return $this->hasMany(Empresa::class, 'rubro_id');
    }
}
