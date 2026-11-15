<?php

namespace App\Services\Systeme;

use RuntimeException;

/**
 * Error controlado al consultar Systeme.io. El mensaje es apto para mostrarse
 * en pantalla y registrarse: nunca contiene la clave ni cabeceras.
 */
class SystemeException extends RuntimeException
{
    public function __construct(string $message, public readonly ?int $status = null)
    {
        parent::__construct($message);
    }
}

