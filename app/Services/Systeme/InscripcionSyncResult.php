<?php

namespace App\Services\Systeme;

/**
 * Resumen de una ejecución de sincronización de la bandeja de inscripciones de Systeme.io.
 */
class InscripcionSyncResult
{
    public int $creados = 0;
    public int $actualizados = 0;
    public int $fallidos = 0;
    public int $vinculadas = 0;
    public int $vinculadasPorIdContacto = 0;
    public int $vinculadasPorEmail = 0;
    public int $pendientes = 0;
    public int $conflictos = 0;
    public int $paginasProcesadas = 0;
    public bool $listadoCompleto = true;
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

    public function procesados(): int
    {
        return $this->creados + $this->actualizados + $this->fallidos;
    }

    /**
     * La sincronización terminó con todas las páginas recorridas sin errores ni cursores repetidos.
     * Que queden inscripciones con vinculación pendiente o en conflicto es un estado normal de la bandeja.
     */
    public function completa(): bool
    {
        return $this->errorGeneral === null
            && $this->listadoCompleto
            && $this->fallidos === 0;
    }

    public function toArray(): array
    {
        return [
            'creados'                    => $this->creados,
            'actualizados'               => $this->actualizados,
            'fallidos'                   => $this->fallidos,
            'vinculadas'                 => $this->vinculadas,
            'vinculadas_por_id_contacto' => $this->vinculadasPorIdContacto,
            'vinculadas_por_email'       => $this->vinculadasPorEmail,
            'pendientes'                 => $this->pendientes,
            'conflictos'                 => $this->conflictos,
            'paginas_procesadas'         => $this->paginasProcesadas,
            'listado_completo'           => $this->listadoCompleto,
            'error_general'              => $this->errorGeneral,
            'completa'                   => $this->completa(),
            'avisos'                     => $this->avisos,
        ];
    }
}

