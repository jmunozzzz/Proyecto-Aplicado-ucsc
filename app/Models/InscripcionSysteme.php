<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InscripcionSysteme extends Model
{
    protected $table = 'inscripcion_systeme';
    public $timestamps = false;

    /** Estados de vinculación con los modelos locales (Persona y Programa). */
    public const VINCULACION_VINCULADA = 'vinculada';
    public const VINCULACION_PENDIENTE = 'pendiente';
    public const VINCULACION_CONFLICTO = 'conflicto';

    /** Vía por la cual se resolvió la vinculación con Persona. */
    public const VIA_ID_CONTACTO = 'id_systeme_contacto';
    public const VIA_EMAIL       = 'email';

    /** Códigos de motivo cuando la vinculación queda pendiente o con conflicto. */
    public const MOTIVO_PERSONA_NO_ENCONTRADA = 'persona_no_encontrada';
    public const MOTIVO_DATOS_INCOMPLETOS     = 'datos_contacto_incompletos';
    public const MOTIVO_CORREO_DUPLICADO      = 'correo_duplicado';
    public const MOTIVO_CORREO_OTRO_CONTACTO  = 'correo_vinculado_otro_contacto';
    public const MOTIVO_CURSO_NO_SINCRONIZADO = 'curso_no_sincronizado';

    /** Tipos de acceso documentados en Systeme.io. */
    public const ACCESS_TYPES = [
        'full_access'             => 'Acceso completo',
        'partial_dripping_access' => 'Acceso parcial por goteo',
        'partial_access'          => 'Acceso parcial',
        'dripping_content'        => 'Contenido por goteo',
    ];

    protected $fillable = [
        'id_systeme_inscripcion',
        'id_systeme_curso',
        'curso_nombre_systeme',
        'id_systeme_contacto',
        'email_contacto',
        'contacto_registrado_en',
        'access_type',
        'systeme_activo',
        'programa_id',
        'persona_id',
        'inscripcion_id',
        'vinculacion_estado',
        'persona_vinculacion_via',
        'vinculacion_motivo_codigo',
        'vinculacion_motivo',
        'fecha_matricula',
        'primera_sincronizacion_en',
        'sincronizado_en',
    ];

    protected $casts = [
        'systeme_activo'            => 'boolean',
        'contacto_registrado_en'    => 'datetime',
        'fecha_matricula'           => 'datetime',
        'primera_sincronizacion_en' => 'datetime',
        'sincronizado_en'           => 'datetime',
    ];

    public function programa(): BelongsTo
    {
        return $this->belongsTo(Programa::class, 'programa_id');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function inscripcionAcademica(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_id');
    }

    public function accessTypeLabel(): string
    {
        return self::ACCESS_TYPES[$this->access_type] ?? str_replace('_', ' ', ucfirst((string) $this->access_type));
    }

    public function vinculacionViaLabel(): ?string
    {
        return match ($this->persona_vinculacion_via) {
            self::VIA_ID_CONTACTO => 'Por ID de contacto',
            self::VIA_EMAIL       => 'Por correo único',
            default               => null,
        };
    }

    public function estaVinculada(): bool
    {
        return $this->vinculacion_estado === self::VINCULACION_VINCULADA
            && $this->programa_id !== null
            && $this->persona_id !== null;
    }
}

