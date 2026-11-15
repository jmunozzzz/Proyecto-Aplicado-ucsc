<?php

namespace App\Services\Systeme;

/**
 * Resumen de una ejecución de «Importar alumnos pendientes» en la bandeja de inscripciones.
 */
class ImportarAlumnosResult
{
    public string $cursoFiltro = '';
    public ?string $cursoNombre = null;
    public int $inscripcionesEvaluadas = 0;
    public int $contactosConsultados = 0;
    public int $personasCreadas = 0;
    public int $vinculadasExistentes = 0;
    public int $pendientes = 0;
    public int $conflictos = 0;
    public int $fallidos = 0;
    public ?string $errorGeneral = null;

    /** @var array<int, array{referencia: string, tipo: string, mensaje: string}> */
    public array $avisos = [];

    public function aviso(string $referencia, string $tipo, string $mensaje): void
    {
        $this->avisos[] = [
            'referencia' => $referencia,
            'tipo'       => $tipo,
            'mensaje'    => $mensaje,
        ];
    }

    public function completa(): bool
    {
        return $this->errorGeneral === null && $this->fallidos === 0;
    }

    public function toArray(): array
    {
        return [
            'curso_filtro'            => $this->cursoFiltro,
            'curso_nombre'            => $this->cursoNombre,
            'inscripciones_evaluadas' => $this->inscripcionesEvaluadas,
            'contactos_consultados'   => $this->contactosConsultados,
            'personas_creadas'        => $this->personasCreadas,
            'vinculadas_existentes'   => $this->vinculadasExistentes,
            'pendientes'              => $this->pendientes,
            'conflictos'              => $this->conflictos,
            'fallidos'                => $this->fallidos,
            'error_general'           => $this->errorGeneral,
            'completa'                => $this->completa(),
            'avisos'                  => $this->avisos,
        ];
    }
}

