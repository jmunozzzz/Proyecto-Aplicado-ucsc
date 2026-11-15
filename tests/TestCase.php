<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Primera comprobación tras arrancar la aplicación y antes de cualquier preparación
        // que escriba en la base (las subclases crean su esquema después de parent::setUp()):
        // las pruebas solo corren sobre SQLite en memoria, nunca sobre la base MariaDB local.
        $conexion = DB::connection();
        if ($conexion->getDriverName() !== 'sqlite' || $conexion->getDatabaseName() !== ':memory:') {
            $this->fail('Las pruebas deben ejecutarse sobre SQLite en memoria (revisa phpunit.xml y que no exista configuración cacheada).');
        }

        // Ninguna prueba puede salir a la red: toda petición HTTP debe estar simulada con Http::fake.
        Http::preventStrayRequests();
    }
}
