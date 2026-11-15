<?php

namespace App\Services\Systeme;

/**
 * Resumen de una ejecución de sincronización de programas.
 */
class ProgramaSyncResult
{
    public int $creados = 0;
    public int $actualizados = 0;
    public int $fallidos = 0;
    public int $estructurasCompletas = 0;
    public int $estructurasPendientes = 0;
    public int $estructurasConError = 0;
    public bool $listadoCompleto = true;
    public ?string $errorGeneral = null;

    /** @var array<int, array{curso: string, tipo: string, mensaje: string}> */
    public array $avisos = [];

    public function aviso(string $curso, string $tipo, string $mensaje): void
    {
        $this->avisos[] = ['curso' => $curso, 'tipo' => $tipo, 'mensaje' => $mensaje];
    }

    public function procesados(): int
    {
        return $this->creados + $this->actualizados + $this->fallidos;
    }

    /** La ejecución terminó sin fallos, sin estructuras pendientes y con el listado completo. */
    public function completa(): bool
    {
        return $this->errorGeneral === null
            && $this->listadoCompleto
            && $this->fallidos === 0
            && $this->estructurasPendientes === 0
            && $this->estructurasConError === 0;
    }

    public function toArray(): array
    {
        return [
            'creados'                => $this->creados,
            'actualizados'           => $this->actualizados,
            'fallidos'               => $this->fallidos,
            'estructuras_completas'  => $this->estructurasCompletas,
            'estructuras_pendientes' => $this->estructurasPendientes,
            'estructuras_con_error'  => $this->estructurasConError,
            'listado_completo'       => $this->listadoCompleto,
            'error_general'          => $this->errorGeneral,
            'completa'               => $this->completa(),
            'avisos'                 => $this->avisos,
        ];
    }
}

