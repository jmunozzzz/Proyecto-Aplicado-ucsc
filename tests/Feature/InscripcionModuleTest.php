<?php

namespace Tests\Feature;

use App\Models\InscripcionSysteme;
use App\Models\Persona;
use App\Models\Programa;
use App\Models\User;
use App\Models\Usuario;
use App\Models\UsuarioRol;
use App\Services\Systeme\InscripcionSyncService;
use App\Support\AdminNavigation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InscripcionModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Garantía estricta: las pruebas de escritura se ejecutan únicamente en SQLite en memoria.
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        Config::set('services.systeme.api_key', 'test-api-key-no-real');

        $this->crearEsquemaDePruebasEnMemoria();
    }

    private function crearEsquemaDePruebasEnMemoria(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('programa', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('codigo')->nullable();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('instructor')->nullable();
            $table->string('imagen_url', 2048)->nullable();
            $table->string('url_publica', 2048)->nullable();
            $table->string('version')->nullable();
            $table->string('linea')->nullable();
            $table->string('modalidad')->nullable();
            $table->string('categoria')->nullable();
            $table->decimal('horas', 8, 2)->nullable();
            $table->string('duracion_descripcion')->nullable();
            $table->text('objetivos')->nullable();
            $table->text('requisitos')->nullable();
            $table->decimal('precio_lista', 14, 2)->nullable();
            $table->char('moneda', 3)->default('CLP');
            $table->string('codigo_sence')->nullable();
            $table->string('id_systeme_curso')->nullable()->unique();
            $table->boolean('systeme_activo')->nullable();
            $table->boolean('systeme_presente')->nullable();
            $table->dateTime('systeme_creado_en')->nullable();
            $table->dateTime('sincronizado_en')->nullable();
            $table->string('sincronizacion_mensaje', 500)->nullable();
            $table->string('estructura_estado', 30)->nullable();
            $table->dateTime('estructura_intentada_en')->nullable();
            $table->dateTime('estructura_confirmada_en')->nullable();
            $table->boolean('activo')->default(true);
        });

        Schema::create('persona', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('tipo_documento')->nullable();
            $table->string('numero_documento')->nullable();
            $table->char('pais_documento', 2)->nullable();
            $table->string('email');
            $table->string('telefono')->nullable();
            $table->char('pais', 2)->nullable();
            $table->string('region')->nullable();
            $table->string('ciudad')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero')->nullable();
            $table->string('nivel_educacional')->nullable();
            $table->string('linkedin', 2048)->nullable();
            $table->string('origen_lead')->default('systeme.io');
            $table->string('estado')->default('lead');
            $table->string('id_systeme_contacto')->nullable()->unique();
            $table->dateTime('creado_en')->nullable();
        });

        Schema::create('empresa', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('razon_social');
        });

        Schema::create('historial_laboral', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('persona_id');
            $table->bigInteger('empresa_id')->nullable();
            $table->string('cargo')->nullable();
            $table->string('area')->nullable();
            $table->string('nivel_jerarquico')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
        });

        Schema::create('cohorte', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('programa_id');
            $table->bigInteger('empresa_id')->nullable();
            $table->string('nombre');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('modalidad')->default('online');
            $table->string('estado')->default('planificada');
        });

        Schema::create('inscripcion', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('persona_id');
            $table->bigInteger('cohorte_id');
            $table->dateTime('fecha')->nullable();
            $table->string('via')->default('pago_individual');
            $table->string('financiamiento')->default('particular');
            $table->string('estado')->default('inscrito');
            $table->decimal('avance_pct', 5, 2)->nullable();
            $table->decimal('nota_final', 3, 2)->nullable();
            $table->string('id_systeme_inscripcion')->nullable()->unique();
            $table->bigInteger('consentimiento_id')->default(1);
        });

        Schema::create('v_asistencia_inscripcion', function (Blueprint $table) {
            $table->bigInteger('inscripcion_id')->primary();
            $table->decimal('asistencia_pct', 5, 2)->nullable();
        });

        Schema::create('usuario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('persona_id')->nullable()->unique();
            $table->string('proveedor_auth');
            $table->string('sujeto_auth');
            $table->boolean('activo')->default(true);
            $table->unique(['proveedor_auth', 'sujeto_auth']);
        });

        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('rol');
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('empresa_scope')->default(0);
        });

        // Ejecutar la migración real de `inscripcion_systeme` sobre la base en memoria.
        $migration = require base_path('database/migrations/2026_11_06_000100_create_inscripcion_systeme_table.php');
        $migration->up();
    }

    private function asignarRol(User $user, string $rol, bool $usuarioActivo = true): void
    {
        $usuario = Usuario::create([
            'proveedor_auth' => Usuario::PROVEEDOR_LOCAL,
            'sujeto_auth'    => (string) $user->id,
            'activo'         => $usuarioActivo,
        ]);

        UsuarioRol::create([
            'usuario_id' => $usuario->id,
            'rol'        => $rol,
        ]);
    }

    private function sembrarDatosBasePrueba(): array
    {
        $evolucion = Programa::create([
            'id'               => 1,
            'nombre'           => 'Evolución Consciente',
            'id_systeme_curso' => '674252',
            'systeme_activo'   => true,
            'systeme_presente' => true,
        ]);

        $claridad = Programa::create([
            'id'               => 2,
            'nombre'           => 'Claridad Ejecutiva',
            'id_systeme_curso' => '674153',
            'systeme_activo'   => false,
            'systeme_presente' => true,
        ]);

        $personaExistente = Persona::create([
            'id'                  => 2,
            'nombres'             => 'Alumno',
            'apellidos'           => 'Registrado',
            'email'               => 'alumno1@ejemplo.cl',
            'origen_lead'         => 'systeme.io',
            'estado'              => 'alumno_activo',
            'id_systeme_contacto' => '445697722',
            'creado_en'           => '2026-10-28 19:17:12',
        ]);

        return [$evolucion, $claridad, $personaExistente];
    }

    /**
     * Devuelve la estructura exacta de las 9 inscripciones comprobadas en Evolución Consciente (#674252).
     *
     * @return array<int, array<string, mixed>>
     */
    private function itemsNueveInscripcionesEvolucionConsciente(): array
    {
        $definiciones = [
            [24803863, 445697722, 'alumno1@ejemplo.cl', '2026-09-28T13:03:07+00:00', 'partial_dripping_access', true],
            [24609113, 448406981, 'alumno2@ejemplo.cl', '2026-10-13T18:56:51+00:00', 'full_access', true],
            [24609112, 448406959, 'alumno3@ejemplo.cl', '2026-10-13T18:56:39+00:00', 'full_access', true],
            [24609111, 448406950, 'alumno4@ejemplo.cl', '2026-10-13T18:56:32+00:00', 'full_access', true],
            [24609110, 448406942, 'alumno5@ejemplo.cl', '2026-10-13T18:56:22+00:00', 'full_access', true],
            [24609108, 448406926, 'alumno6@ejemplo.cl', '2026-10-13T18:56:13+00:00', 'full_access', true],
            [24609105, 448406899, 'alumno7@ejemplo.cl', '2026-10-13T18:55:59+00:00', 'full_access', true],
            [24609103, 448406875, 'alumno8@ejemplo.cl', '2026-10-13T18:55:44+00:00', 'full_access', true],
            [24540790, 447325273, 'alumno9@ejemplo.cl', '2026-10-07T12:51:55+00:00', 'full_access', true],
        ];

        return array_map(fn (array $d) => [
            'id'      => $d[0],
            'contact' => [
                'id'                => $d[1],
                'email'             => $d[2],
                'registeredAt'      => $d[3],
                'locale'            => 'es',
                'sourceURL'         => null,
                'unsubscribed'      => false,
                'bounced'           => false,
                'needsConfirmation' => false,
            ],
            'course' => [
                'id'     => 674252,
                'locale' => 'es',
                'path'   => 'evolucionconsciente',
                'active' => true,
                'name'   => 'Evolución Consciente',
            ],
            'accessType' => $d[4],
            'active'     => $d[5],
        ], $definiciones);
    }

    public function test_importa_las_9_inscripciones_de_evolucion_consciente_vincula_persona_existente_por_id_y_no_duplica(): void
    {
        [$evolucion, , $personaExistente] = $this->sembrarDatosBasePrueba();
        $items = $this->itemsNueveInscripcionesEvolucionConsciente();

        Http::fake(function ($request) use ($items) {
            $this->assertStringContainsString('/api/school/enrollments', $request->url());
            $this->assertSame('100', (string) ($request['limit'] ?? ''));
            $this->assertSame('desc', (string) ($request['order'] ?? ''));

            return Http::response([
                'items'   => $items,
                'hasMore' => false,
            ], 200);
        });

        /** @var InscripcionSyncService $service */
        $service = $this->app->make(InscripcionSyncService::class);

        // Primera sincronización
        $res1 = $service->sincronizar();

        $this->assertTrue($res1->completa());
        $this->assertTrue($res1->listadoCompleto);
        $this->assertNull($res1->errorGeneral);
        $this->assertSame(1, $res1->paginasProcesadas);
        $this->assertSame(9, $res1->creados);
        $this->assertSame(0, $res1->actualizados);
        $this->assertSame(0, $res1->fallidos);
        $this->assertSame(1, $res1->vinculadas);
        $this->assertSame(1, $res1->vinculadasPorIdContacto);
        $this->assertSame(0, $res1->vinculadasPorEmail);
        $this->assertSame(8, $res1->pendientes);
        $this->assertSame(0, $res1->conflictos);

        $this->assertSame(9, InscripcionSysteme::count());

        // Verificar la inscripción #24803863 vinculada a Persona #2 por id_systeme_contacto
        $vinculada = InscripcionSysteme::where('id_systeme_inscripcion', '24803863')->firstOrFail();
        $this->assertSame('674252', $vinculada->id_systeme_curso);
        $this->assertSame('445697722', $vinculada->id_systeme_contacto);
        $this->assertSame('alumno1@ejemplo.cl', $vinculada->email_contacto);
        $this->assertSame('partial_dripping_access', $vinculada->access_type);
        $this->assertTrue($vinculada->systeme_activo);
        $this->assertSame($evolucion->id, $vinculada->programa_id);
        $this->assertSame($personaExistente->id, $vinculada->persona_id);
        $this->assertSame(InscripcionSysteme::VINCULACION_VINCULADA, $vinculada->vinculacion_estado);
        $this->assertSame(InscripcionSysteme::VIA_ID_CONTACTO, $vinculada->persona_vinculacion_via);
        $this->assertNull($vinculada->vinculacion_motivo_codigo);
        $this->assertNull($vinculada->vinculacion_motivo);

        // Fechas separadas: contacto_registrado_en vs sincronizado_en vs fecha_matricula
        $this->assertNotNull($vinculada->contacto_registrado_en);
        $this->assertSame('2026-09-28 13:03:07', $vinculada->contacto_registrado_en->utc()->format('Y-m-d H:i:s'));
        $this->assertNotNull($vinculada->sincronizado_en);
        $this->assertNull($vinculada->fecha_matricula);

        // Verificar las otras 8 inscripciones pendientes de persona
        $pendientes = InscripcionSysteme::where('id_systeme_inscripcion', '!=', '24803863')->get();
        $this->assertCount(8, $pendientes);
        foreach ($pendientes as $p) {
            $this->assertSame($evolucion->id, $p->programa_id);
            $this->assertNull($p->persona_id);
            $this->assertSame(InscripcionSysteme::VINCULACION_PENDIENTE, $p->vinculacion_estado);
            $this->assertSame(InscripcionSysteme::MOTIVO_PERSONA_NO_ENCONTRADA, $p->vinculacion_motivo_codigo);
            $this->assertNotEmpty($p->vinculacion_motivo);
            $this->assertSame('full_access', $p->access_type);
            $this->assertTrue($p->systeme_activo);
        }

        // Asignar una fecha_matricula local para comprobar que una segunda sincronización no la sobrescribe
        $fechaMatriculaManual = Carbon::parse('2026-10-28 19:17:12');
        $vinculada->update(['fecha_matricula' => $fechaMatriculaManual]);

        // Segunda sincronización: no genera duplicados y conserva fecha_matricula separada de sincronizado_en
        $res2 = $service->sincronizar();

        $this->assertTrue($res2->completa());
        $this->assertSame(0, $res2->creados);
        $this->assertSame(9, $res2->actualizados);
        $this->assertSame(9, InscripcionSysteme::count());

        $vinculada->refresh();
        $this->assertSame('2026-10-28 19:17:12', $vinculada->fecha_matricula->format('Y-m-d H:i:s'));
    }

    public function test_vinculacion_por_correo_unico_y_conflictos_por_correo_ambiguo_o_id_incompatible(): void
    {
        $this->sembrarDatosBasePrueba();

        // Caso A: Persona única sin id_systeme_contacto (se debe vincular por email)
        $personaUnicaPorCorreo = Persona::create([
            'nombres'             => 'Camila',
            'apellidos'           => 'Única',
            'email'               => 'unico@ejemplo.cl',
            'origen_lead'         => 'web',
            'estado'              => 'lead',
            'id_systeme_contacto' => null,
        ]);

        // Caso B: Dos personas con el mismo correo (correo ambiguo -> conflicto)
        Persona::create([
            'nombres'             => 'Duplicado',
            'apellidos'           => 'Uno',
            'email'               => 'ambiguo@ejemplo.cl',
            'origen_lead'         => 'web',
            'estado'              => 'lead',
            'id_systeme_contacto' => null,
        ]);
        Persona::create([
            'nombres'             => 'Duplicado',
            'apellidos'           => 'Dos',
            'email'               => 'ambiguo@ejemplo.cl',
            'origen_lead'         => 'manual',
            'estado'              => 'alumno_activo',
            'id_systeme_contacto' => null,
        ]);

        // Caso C: Persona con el mismo correo pero ya vinculada a otro id_systeme_contacto distinto
        Persona::create([
            'nombres'             => 'Otro',
            'apellidos'           => 'Contacto',
            'email'               => 'incompatible@ejemplo.cl',
            'origen_lead'         => 'systeme.io',
            'estado'              => 'alumno_activo',
            'id_systeme_contacto' => '999999999',
        ]);

        Http::fake([
            'https://api.systeme.io/api/school/enrollments*' => Http::response([
                'items' => [
                    [
                        'id'      => 30000003,
                        'contact' => [
                            'id'           => 50000003,
                            'email'        => '  UNICO@ejemplo.cl ',
                            'registeredAt' => '2026-10-15T10:00:00+00:00',
                        ],
                        'course' => [
                            'id'     => 674252,
                            'active' => true,
                            'name'   => 'Evolución Consciente',
                        ],
                        'accessType' => 'full_access',
                        'active'     => true,
                    ],
                    [
                        'id'      => 30000002,
                        'contact' => [
                            'id'           => 50000002,
                            'email'        => 'ambiguo@ejemplo.cl',
                            'registeredAt' => '2026-10-15T09:00:00+00:00',
                        ],
                        'course' => [
                            'id'     => 674252,
                            'active' => true,
                            'name'   => 'Evolución Consciente',
                        ],
                        'accessType' => 'full_access',
                        'active'     => true,
                    ],
                    [
                        'id'      => 30000001,
                        'contact' => [
                            'id'           => 50000001,
                            'email'        => 'incompatible@ejemplo.cl',
                            'registeredAt' => '2026-10-15T08:00:00+00:00',
                        ],
                        'course' => [
                            'id'     => 674252,
                            'active' => true,
                            'name'   => 'Evolución Consciente',
                        ],
                        'accessType' => 'full_access',
                        'active'     => true,
                    ],
                ],
                'hasMore' => false,
            ], 200),
        ]);

        /** @var InscripcionSyncService $service */
        $service = $this->app->make(InscripcionSyncService::class);
        $res = $service->sincronizar();

        $this->assertTrue($res->completa());
        $this->assertSame(3, $res->creados);
        $this->assertSame(1, $res->vinculadas);
        $this->assertSame(1, $res->vinculadasPorEmail);
        $this->assertSame(2, $res->conflictos);

        $porCorreoUnico = InscripcionSysteme::where('id_systeme_inscripcion', '30000003')->firstOrFail();
        $this->assertSame($personaUnicaPorCorreo->id, $porCorreoUnico->persona_id);
        $this->assertSame(InscripcionSysteme::VINCULACION_VINCULADA, $porCorreoUnico->vinculacion_estado);
        $this->assertSame(InscripcionSysteme::VIA_EMAIL, $porCorreoUnico->persona_vinculacion_via);
        $this->assertNull($porCorreoUnico->vinculacion_motivo);

        $porCorreoAmbiguo = InscripcionSysteme::where('id_systeme_inscripcion', '30000002')->firstOrFail();
        $this->assertNull($porCorreoAmbiguo->persona_id);
        $this->assertSame(InscripcionSysteme::VINCULACION_CONFLICTO, $porCorreoAmbiguo->vinculacion_estado);
        $this->assertSame(InscripcionSysteme::MOTIVO_CORREO_DUPLICADO, $porCorreoAmbiguo->vinculacion_motivo_codigo);
        $this->assertStringContainsString('varias personas', (string) $porCorreoAmbiguo->vinculacion_motivo);

        $porIdIncompatible = InscripcionSysteme::where('id_systeme_inscripcion', '30000001')->firstOrFail();
        $this->assertNull($porIdIncompatible->persona_id);
        $this->assertSame(InscripcionSysteme::VINCULACION_CONFLICTO, $porIdIncompatible->vinculacion_estado);
        $this->assertSame(InscripcionSysteme::MOTIVO_CORREO_OTRO_CONTACTO, $porIdIncompatible->vinculacion_motivo_codigo);
        $this->assertStringContainsString('#999999999', (string) $porIdIncompatible->vinculacion_motivo);
    }

    public function test_paginacion_multipagina_decreciente_e_informe_de_paginacion_incompleta_y_ausencia_no_cambia_acceso(): void
    {
        $this->sembrarDatosBasePrueba();
        $items = $this->itemsNueveInscripcionesEvolucionConsciente();

        $pagina1 = array_slice($items, 0, 5); // IDs: 24803863 .. 24609110 (último = 24609110)
        $pagina2 = array_slice($items, 5, 4); // IDs: 24609108 .. 24540790

        $escenario = 'multipagina_ok';

        Http::fake(function ($request) use ($pagina1, $pagina2, &$escenario) {
            $startingAfter = $request['startingAfter'] ?? null;

            if ($escenario === 'multipagina_ok') {
                if ($startingAfter === null) {
                    return Http::response(['items' => $pagina1, 'hasMore' => true], 200);
                }
                $this->assertSame('24609110', (string) $startingAfter);

                return Http::response(['items' => $pagina2, 'hasMore' => false], 200);
            }

            if ($escenario === 'cursor_no_decreciente') {
                if ($startingAfter === null) {
                    return Http::response(['items' => $pagina1, 'hasMore' => true], 200);
                }
                // Devuelve un ítem con ID igual al cursor startingAfter (no disminuye estrictamente)
                $itemRepetido = $pagina1[4]; // ID 24609110

                return Http::response(['items' => [$itemRepetido], 'hasMore' => false], 200);
            }

            if ($escenario === 'fallo_pagina_2') {
                if ($startingAfter === null) {
                    return Http::response(['items' => $pagina1, 'hasMore' => true], 200);
                }

                return Http::response(['detail' => 'Server error'], 500);
            }

            // Escenario: ausencia en respuesta posterior
            return Http::response(['items' => $pagina1, 'hasMore' => false], 200);
        });

        /** @var InscripcionSyncService $service */
        $service = $this->app->make(InscripcionSyncService::class);

        // 1. Paginación multipágina válida con cursor decreciente
        $resOk = $service->sincronizar();
        $this->assertTrue($resOk->completa());
        $this->assertTrue($resOk->listadoCompleto);
        $this->assertSame(2, $resOk->paginasProcesadas);
        $this->assertSame(9, InscripcionSysteme::count());

        // 2. Cursor repetido / no estrictamente decreciente -> informa sincronización incompleta
        $escenario = 'cursor_no_decreciente';
        $resCursor = $service->sincronizar();
        $this->assertFalse($resCursor->completa());
        $this->assertFalse($resCursor->listadoCompleto);
        $this->assertNotEmpty($resCursor->avisos);
        $this->assertStringContainsString('Sincronización incompleta', $resCursor->avisos[0]['mensaje']);

        // 3. Fallo HTTP en segunda página -> informa sincronización incompleta
        $escenario = 'fallo_pagina_2';
        $resFallo = $service->sincronizar();
        $this->assertFalse($resFallo->completa());
        $this->assertFalse($resFallo->listadoCompleto);
        $this->assertNotEmpty($resFallo->avisos);
        $this->assertStringContainsString('Sincronización incompleta', $resFallo->avisos[0]['mensaje']);

        // 4. La ausencia de las últimas 4 inscripciones en una respuesta no cambia su estado de acceso anterior
        $escenario = 'ausencia';
        $resAusencia = $service->sincronizar();
        $this->assertTrue($resAusencia->completa());
        $this->assertSame(9, InscripcionSysteme::count());

        $ausente = InscripcionSysteme::where('id_systeme_inscripcion', '24540790')->firstOrFail();
        $this->assertTrue($ausente->systeme_activo);
        $this->assertSame('full_access', $ausente->access_type);
    }

    public function test_autorizacion_protege_listado_y_sincronizacion_para_administradores_activos_y_muestra_ficha_persona(): void
    {
        [, , $personaExistente] = $this->sembrarDatosBasePrueba();
        $items = $this->itemsNueveInscripcionesEvolucionConsciente();

        Http::fake([
            'https://api.systeme.io/api/school/enrollments*' => Http::response([
                'items'   => $items,
                'hasMore' => false,
            ], 200),
        ]);

        // Invitado redirige a login
        $this->get('/inscripciones')->assertRedirect('/login');
        $this->post('/inscripciones/sincronizar')->assertRedirect('/login');

        // Usuario activo sin rol administrador (facilitador): HTTP 403 tanto en listado como en sincronizar
        $facilitador = User::factory()->create(['is_active' => true, 'email' => 'facilitador@neuromanagement.cl']);
        $this->asignarRol($facilitador, UsuarioRol::ROL_FACILITADOR);

        $this->actingAs($facilitador)->get('/inscripciones')->assertForbidden();
        $this->actingAs($facilitador)->post('/inscripciones/sincronizar')->assertForbidden();

        // Verificar que el ítem 'Inscripciones' no aparece en el menú para un usuario sin permiso
        $this->actingAs($facilitador);
        $labelsFacilitador = collect(AdminNavigation::sections())
            ->flatMap(fn ($s) => $s['items'])
            ->pluck('label')
            ->all();
        $this->assertNotContains('Inscripciones', $labelsFacilitador);

        // Usuario administrador pero inactivo: HTTP 403
        $adminInactivo = User::factory()->create(['is_active' => false, 'email' => 'inactivo@neuromanagement.cl']);
        $this->asignarRol($adminInactivo, UsuarioRol::ROL_ADMINISTRADOR);

        $this->actingAs($adminInactivo)->get('/inscripciones')->assertForbidden();
        $this->actingAs($adminInactivo)->post('/inscripciones/sincronizar')->assertForbidden();

        // Administrador activo: puede sincronizar, consultar el listado con filtros y ver la inscripción en la ficha de la persona
        $admin = User::factory()->create(['is_active' => true, 'email' => 'admin@neuromanagement.cl']);
        $this->asignarRol($admin, UsuarioRol::ROL_ADMINISTRADOR);

        $this->actingAs($admin)
            ->post('/inscripciones/sincronizar')
            ->assertRedirect('/inscripciones');

        $this->assertSame(9, InscripcionSysteme::count());

        // Listado completo
        $this->actingAs($admin)
            ->get('/inscripciones')
            ->assertOk()
            ->assertSee('Bandeja de')
            ->assertSee('24803863')
            ->assertSee('Alumno Registrado')
            ->assertSee('Acceso parcial por goteo');

        // Filtro por estado de vinculación = vinculada (solo #24803863)
        $this->actingAs($admin)
            ->get('/inscripciones?vinculacion=vinculada')
            ->assertOk()
            ->assertSee('24803863')
            ->assertDontSee('24609113');

        // Filtro por estado de vinculación = pendiente (las otras 8)
        $this->actingAs($admin)
            ->get('/inscripciones?vinculacion=pendiente')
            ->assertOk()
            ->assertSee('24609113')
            ->assertDontSee('24803863');

        // Filtro por curso = 674252 y acceso = activo
        $this->actingAs($admin)
            ->get('/inscripciones?curso=674252&acceso=activo')
            ->assertOk()
            ->assertSee('24803863')
            ->assertSee('24540790');

        // Ficha de la persona (#2): muestra su inscripción vinculada de Systeme.io
        $this->actingAs($admin)
            ->get("/personas/{$personaExistente->id}")
            ->assertOk()
            ->assertSee('Inscripciones vinculadas (Systeme.io)')
            ->assertSee('#24803863')
            ->assertSee('Evolución Consciente')
            ->assertSee('Acceso parcial por goteo')
            ->assertSee('Por ID de contacto');
    }

    public function test_importar_alumnos_pendientes_crea_persona_con_contacto_completo_indica_campos_faltantes_y_consulta_cada_contacto_una_vez(): void
    {
        [$evolucion, $claridad, $personaExistente] = $this->sembrarDatosBasePrueba();
        $items = $this->itemsNueveInscripcionesEvolucionConsciente();

        // Agregar una segunda inscripción pendiente para el mismo contacto #447325273 en Evolución Consciente
        // y una inscripción pendiente de otro curso (#674153) que no debe procesarse al importar #674252.
        $items[] = [
            'id'      => 24540789,
            'contact' => [
                'id'           => 447325273,
                'email'        => 'alumno9@ejemplo.cl',
                'registeredAt' => '2026-10-07T12:51:55+00:00',
            ],
            'course' => [
                'id'     => 674252,
                'active' => true,
                'name'   => 'Evolución Consciente',
            ],
            'accessType' => 'full_access',
            'active'     => true,
        ];
        $items[] = [
            'id'      => 24500001,
            'contact' => [
                'id'           => 449999999,
                'email'        => 'otrocurso@ejemplo.cl',
                'registeredAt' => '2026-10-01T10:00:00+00:00',
            ],
            'course' => [
                'id'     => 674153,
                'active' => false,
                'name'   => 'Claridad Ejecutiva',
            ],
            'accessType' => 'full_access',
            'active'     => true,
        ];

        $llamadasPorContacto = [];

        Http::fake(function ($request) use ($items, &$llamadasPorContacto) {
            $this->assertSame('GET', $request->method(), 'La API de Systeme.io solo debe usarse en modo lectura (GET).');
            $url = $request->url();

            if (str_contains($url, '/api/school/enrollments')) {
                return Http::response(['items' => $items, 'hasMore' => false], 200);
            }

            if (preg_match('#/api/contacts/(\d+)$#', $url, $matches)) {
                $contactId = $matches[1];
                $llamadasPorContacto[$contactId] = ($llamadasPorContacto[$contactId] ?? 0) + 1;

                // Contacto completo (#447325273)
                if ($contactId === '447325273') {
                    return Http::response([
                        'id'     => 447325273,
                        'email'  => 'alumno9@ejemplo.cl',
                        'fields' => [
                            ['slug' => 'first_name', 'value' => 'María'],
                            ['slug' => 'surname', 'value' => 'González'],
                        ],
                    ], 200);
                }

                // Contacto con apellido pero sin nombre (#448406981)
                if ($contactId === '448406981') {
                    return Http::response([
                        'id'     => 448406981,
                        'email'  => 'alumno2@ejemplo.cl',
                        'fields' => [
                            ['slug' => 'surname', 'value' => 'Pérez'],
                        ],
                    ], 200);
                }

                // Los otros 6 contactos devuelven fields vacío (faltan first_name y surname)
                return Http::response([
                    'id'     => (int) $contactId,
                    'email'  => "contacto{$contactId}@ejemplo.cl",
                    'fields' => [],
                ], 200);
            }

            return Http::response(['detail' => 'Not found'], 404);
        });

        /** @var InscripcionSyncService $service */
        $service = $this->app->make(InscripcionSyncService::class);
        $service->sincronizar();

        $this->assertSame(1, Persona::count());

        // Ejecutar importación de alumnos pendientes (por defecto Evolución Consciente #674252)
        $res = $service->importarAlumnosPendientes();

        $this->assertTrue($res->completa());
        $this->assertSame('674252', $res->cursoFiltro);
        $this->assertSame('Evolución Consciente', $res->cursoNombre);
        $this->assertSame(9, $res->inscripcionesEvaluadas); // 8 contactos distintos (uno con 2 inscripciones)
        $this->assertSame(8, $res->contactosConsultados);
        $this->assertSame(1, $res->personasCreadas);
        $this->assertSame(1, $res->vinculadasExistentes);
        $this->assertSame(7, $res->pendientes);
        $this->assertSame(0, $res->conflictos);
        $this->assertSame(0, $res->fallidos);

        // Verificar que #447325273 se consultó exactamente 1 vez pese a estar en 2 inscripciones pendientes,
        // que #445697722 (ya vinculada) no se consultó y que #449999999 (otro curso) tampoco se consultó.
        $this->assertSame(1, $llamadasPorContacto['447325273'] ?? 0);
        $this->assertArrayNotHasKey('445697722', $llamadasPorContacto);
        $this->assertArrayNotHasKey('449999999', $llamadasPorContacto);
        foreach ($llamadasPorContacto as $conteo) {
            $this->assertSame(1, $conteo);
        }

        // Verificar la ficha Persona creada para #447325273 y las 2 inscripciones vinculadas
        $this->assertSame(2, Persona::count());
        $nuevaPersona = Persona::where('id_systeme_contacto', '447325273')->firstOrFail();
        $this->assertSame('María', $nuevaPersona->nombres);
        $this->assertSame('González', $nuevaPersona->apellidos);
        $this->assertSame('alumno9@ejemplo.cl', $nuevaPersona->email);
        $this->assertSame('systeme.io', $nuevaPersona->origen_lead);
        $this->assertSame('alumno_activo', $nuevaPersona->estado);

        $insc1 = InscripcionSysteme::where('id_systeme_inscripcion', '24540790')->firstOrFail();
        $this->assertSame($nuevaPersona->id, $insc1->persona_id);
        $this->assertSame($evolucion->id, $insc1->programa_id);
        $this->assertSame(InscripcionSysteme::VINCULACION_VINCULADA, $insc1->vinculacion_estado);
        $this->assertSame(InscripcionSysteme::VIA_ID_CONTACTO, $insc1->persona_vinculacion_via);
        $this->assertNull($insc1->vinculacion_motivo);

        $insc2 = InscripcionSysteme::where('id_systeme_inscripcion', '24540789')->firstOrFail();
        $this->assertSame($nuevaPersona->id, $insc2->persona_id);
        $this->assertSame(InscripcionSysteme::VINCULACION_VINCULADA, $insc2->vinculacion_estado);

        // Verificar que la inscripción sin first_name (#24609113) indica específicamente qué campo falta
        $sinNombre = InscripcionSysteme::where('id_systeme_inscripcion', '24609113')->firstOrFail();
        $this->assertNull($sinNombre->persona_id);
        $this->assertSame(InscripcionSysteme::VINCULACION_PENDIENTE, $sinNombre->vinculacion_estado);
        $this->assertSame(InscripcionSysteme::MOTIVO_DATOS_INCOMPLETOS, $sinNombre->vinculacion_motivo_codigo);
        $this->assertStringContainsString('nombre (first_name)', (string) $sinNombre->vinculacion_motivo);
        $this->assertStringNotContainsString('apellido (surname)', (string) $sinNombre->vinculacion_motivo);

        // Verificar que la inscripción con fields vacío (#24609112) indica que faltan nombre y apellido
        $sinAmbos = InscripcionSysteme::where('id_systeme_inscripcion', '24609112')->firstOrFail();
        $this->assertNull($sinAmbos->persona_id);
        $this->assertSame(InscripcionSysteme::VINCULACION_PENDIENTE, $sinAmbos->vinculacion_estado);
        $this->assertSame(InscripcionSysteme::MOTIVO_DATOS_INCOMPLETOS, $sinAmbos->vinculacion_motivo_codigo);
        $this->assertStringContainsString('nombre (first_name)', (string) $sinAmbos->vinculacion_motivo);
        $this->assertStringContainsString('apellido (surname)', (string) $sinAmbos->vinculacion_motivo);

        // La inscripción de Claridad Ejecutiva (#24500001) sigue intacta sin procesar
        $otroCurso = InscripcionSysteme::where('id_systeme_inscripcion', '24500001')->firstOrFail();
        $this->assertSame($claridad->id, $otroCurso->programa_id);
        $this->assertNull($otroCurso->persona_id);
        $this->assertSame(InscripcionSysteme::MOTIVO_PERSONA_NO_ENCONTRADA, $otroCurso->vinculacion_motivo_codigo);
    }

    public function test_importar_alumnos_pendientes_correo_en_conflicto_vincula_ficha_existente_y_es_idempotente(): void
    {
        [$evolucion] = $this->sembrarDatosBasePrueba();

        // 1. Ficha Persona existente localmente por correo (con datos locales propios que NO deben sobrescribirse)
        $personaLocal = Persona::create([
            'nombres'             => 'Roberto Local',
            'apellidos'           => 'Soto Original',
            'email'               => 'roberto@ejemplo.cl',
            'telefono'            => '+56912345678',
            'origen_lead'         => 'manual',
            'estado'              => 'egresado',
            'id_systeme_contacto' => null,
        ]);

        // 2. Dos personas con el mismo correo -> conflicto por correo duplicado
        Persona::create([
            'nombres'             => 'Ana Uno',
            'apellidos'           => 'Conflicto',
            'email'               => 'duplicado@ejemplo.cl',
            'origen_lead'         => 'web',
            'estado'              => 'lead',
            'id_systeme_contacto' => null,
        ]);
        Persona::create([
            'nombres'             => 'Ana Dos',
            'apellidos'           => 'Conflicto',
            'email'               => 'duplicado@ejemplo.cl',
            'origen_lead'         => 'web',
            'estado'              => 'lead',
            'id_systeme_contacto' => null,
        ]);

        // 3. Persona con correo ya vinculado a otro id_systeme_contacto -> conflicto por ID incompatible
        Persona::create([
            'nombres'             => 'Carlos',
            'apellidos'           => 'OtroId',
            'email'               => 'carlos@ejemplo.cl',
            'origen_lead'         => 'systeme.io',
            'estado'              => 'alumno_activo',
            'id_systeme_contacto' => '888888888',
        ]);

        $ahora = Carbon::now();

        // Sembrar 4 inscripciones pendientes en la bandeja para Evolución Consciente (#674252):
        // - #90001 (contacto 70001): contacto nuevo completo -> creará Persona
        // - #90002 (contacto 70002): coincide por correo con $personaLocal -> vinculará existente sin sobrescribir
        // - #90003 (contacto 70003): correo duplicado -> conflicto
        // - #90004 (contacto 70004): correo con otro id_systeme_contacto -> conflicto
        InscripcionSysteme::create([
            'id_systeme_inscripcion'    => '90001',
            'id_systeme_curso'          => '674252',
            'curso_nombre_systeme'      => 'Evolución Consciente',
            'id_systeme_contacto'       => '70001',
            'email_contacto'            => 'nuevo@ejemplo.cl',
            'access_type'               => 'full_access',
            'systeme_activo'            => true,
            'programa_id'               => $evolucion->id,
            'persona_id'                => null,
            'vinculacion_estado'        => InscripcionSysteme::VINCULACION_PENDIENTE,
            'vinculacion_motivo_codigo' => InscripcionSysteme::MOTIVO_PERSONA_NO_ENCONTRADA,
            'primera_sincronizacion_en' => $ahora,
            'sincronizado_en'           => $ahora,
        ]);

        InscripcionSysteme::create([
            'id_systeme_inscripcion'    => '90002',
            'id_systeme_curso'          => '674252',
            'curso_nombre_systeme'      => 'Evolución Consciente',
            'id_systeme_contacto'       => '70002',
            'email_contacto'            => 'roberto@ejemplo.cl',
            'access_type'               => 'full_access',
            'systeme_activo'            => true,
            'programa_id'               => $evolucion->id,
            'persona_id'                => null,
            'vinculacion_estado'        => InscripcionSysteme::VINCULACION_PENDIENTE,
            'vinculacion_motivo_codigo' => InscripcionSysteme::MOTIVO_PERSONA_NO_ENCONTRADA,
            'primera_sincronizacion_en' => $ahora,
            'sincronizado_en'           => $ahora,
        ]);

        InscripcionSysteme::create([
            'id_systeme_inscripcion'    => '90003',
            'id_systeme_curso'          => '674252',
            'curso_nombre_systeme'      => 'Evolución Consciente',
            'id_systeme_contacto'       => '70003',
            'email_contacto'            => 'duplicado@ejemplo.cl',
            'access_type'               => 'full_access',
            'systeme_activo'            => true,
            'programa_id'               => $evolucion->id,
            'persona_id'                => null,
            'vinculacion_estado'        => InscripcionSysteme::VINCULACION_PENDIENTE,
            'vinculacion_motivo_codigo' => InscripcionSysteme::MOTIVO_PERSONA_NO_ENCONTRADA,
            'primera_sincronizacion_en' => $ahora,
            'sincronizado_en'           => $ahora,
        ]);

        InscripcionSysteme::create([
            'id_systeme_inscripcion'    => '90004',
            'id_systeme_curso'          => '674252',
            'curso_nombre_systeme'      => 'Evolución Consciente',
            'id_systeme_contacto'       => '70004',
            'email_contacto'            => 'carlos@ejemplo.cl',
            'access_type'               => 'full_access',
            'systeme_activo'            => true,
            'programa_id'               => $evolucion->id,
            'persona_id'                => null,
            'vinculacion_estado'        => InscripcionSysteme::VINCULACION_PENDIENTE,
            'vinculacion_motivo_codigo' => InscripcionSysteme::MOTIVO_PERSONA_NO_ENCONTRADA,
            'primera_sincronizacion_en' => $ahora,
            'sincronizado_en'           => $ahora,
        ]);

        Http::fake([
            'https://api.systeme.io/api/contacts/70001' => Http::response([
                'id'     => 70001,
                'email'  => 'nuevo@ejemplo.cl',
                'fields' => [
                    ['slug' => 'first_name', 'value' => 'Elena'],
                    ['slug' => 'surname', 'value' => 'Valdés'],
                ],
            ], 200),
            'https://api.systeme.io/api/contacts/70002' => Http::response([
                'id'     => 70002,
                'email'  => 'roberto@ejemplo.cl',
                'fields' => [
                    ['slug' => 'first_name', 'value' => 'Nombre Remoto Distinto'],
                    ['slug' => 'surname', 'value' => 'Apellido Remoto Distinto'],
                ],
            ], 200),
            'https://api.systeme.io/api/contacts/70003' => Http::response([
                'id'     => 70003,
                'email'  => 'duplicado@ejemplo.cl',
                'fields' => [
                    ['slug' => 'first_name', 'value' => 'Ana'],
                    ['slug' => 'surname', 'value' => 'Conflicto'],
                ],
            ], 200),
            'https://api.systeme.io/api/contacts/70004' => Http::response([
                'id'     => 70004,
                'email'  => 'carlos@ejemplo.cl',
                'fields' => [
                    ['slug' => 'first_name', 'value' => 'Carlos'],
                    ['slug' => 'surname', 'value' => 'Otro'],
                ],
            ], 200),
        ]);

        $totalPersonasAntes = Persona::count();

        /** @var InscripcionSyncService $service */
        $service = $this->app->make(InscripcionSyncService::class);

        // Primera ejecución
        $res1 = $service->importarAlumnosPendientes('674252');

        $this->assertTrue($res1->completa());
        $this->assertSame(1, $res1->personasCreadas);
        $this->assertSame(1, $res1->vinculadasExistentes);
        $this->assertSame(0, $res1->pendientes);
        $this->assertSame(2, $res1->conflictos);
        $this->assertSame($totalPersonasAntes + 1, Persona::count());

        // Verificar que la ficha existente ($personaLocal) conservó todos sus datos locales intactos
        $personaLocal->refresh();
        $this->assertSame('Roberto Local', $personaLocal->nombres);
        $this->assertSame('Soto Original', $personaLocal->apellidos);
        $this->assertSame('roberto@ejemplo.cl', $personaLocal->email);
        $this->assertSame('+56912345678', $personaLocal->telefono);
        $this->assertSame('manual', $personaLocal->origen_lead);
        $this->assertSame('egresado', $personaLocal->estado);

        $inscExistente = InscripcionSysteme::where('id_systeme_inscripcion', '90002')->firstOrFail();
        $this->assertSame($personaLocal->id, $inscExistente->persona_id);
        $this->assertSame(InscripcionSysteme::VINCULACION_VINCULADA, $inscExistente->vinculacion_estado);
        $this->assertSame(InscripcionSysteme::VIA_EMAIL, $inscExistente->persona_vinculacion_via);

        // Verificar conflictos en #90003 y #90004
        $inscDuplicado = InscripcionSysteme::where('id_systeme_inscripcion', '90003')->firstOrFail();
        $this->assertNull($inscDuplicado->persona_id);
        $this->assertSame(InscripcionSysteme::VINCULACION_CONFLICTO, $inscDuplicado->vinculacion_estado);
        $this->assertSame(InscripcionSysteme::MOTIVO_CORREO_DUPLICADO, $inscDuplicado->vinculacion_motivo_codigo);

        $inscOtroId = InscripcionSysteme::where('id_systeme_inscripcion', '90004')->firstOrFail();
        $this->assertNull($inscOtroId->persona_id);
        $this->assertSame(InscripcionSysteme::VINCULACION_CONFLICTO, $inscOtroId->vinculacion_estado);
        $this->assertSame(InscripcionSysteme::MOTIVO_CORREO_OTRO_CONTACTO, $inscOtroId->vinculacion_motivo_codigo);

        // Editar localmente la nueva persona creada (#70001) y ejecutar una segunda vez:
        // No debe crear duplicados ni sobrescribir los datos locales editados.
        $elena = Persona::where('id_systeme_contacto', '70001')->firstOrFail();
        $elena->update([
            'nombres'  => 'Elena Editada Localmente',
            'telefono' => '+56987654321',
        ]);

        // Además desvinculamos temporalmente #90001 para simular una re-ejecución sobre la misma fila
        InscripcionSysteme::where('id_systeme_inscripcion', '90001')->update([
            'persona_id'         => null,
            'vinculacion_estado' => InscripcionSysteme::VINCULACION_PENDIENTE,
        ]);

        $res2 = $service->importarAlumnosPendientes('674252');

        $this->assertTrue($res2->completa());
        $this->assertSame(0, $res2->personasCreadas);
        $this->assertSame(1, $res2->vinculadasExistentes);
        $this->assertSame(0, $res2->pendientes);
        $this->assertSame(2, $res2->conflictos);
        $this->assertSame($totalPersonasAntes + 1, Persona::count());

        $elena->refresh();
        $this->assertSame('Elena Editada Localmente', $elena->nombres);
        $this->assertSame('Valdés', $elena->apellidos);
        $this->assertSame('+56987654321', $elena->telefono);

        // Tercera ejecución inmediata con #90001 y #90002 ya vinculadas: 0 creadas, 0 sobrescritas
        $res3 = $service->importarAlumnosPendientes('674252');
        $this->assertSame(0, $res3->personasCreadas);
        $this->assertSame(0, $res3->vinculadasExistentes);
        $this->assertSame(2, $res3->conflictos);
        $this->assertSame($totalPersonasAntes + 1, Persona::count());
    }

    public function test_endpoint_http_importar_alumnos_autorizacion_y_resumen_en_bandeja(): void
    {
        [$evolucion] = $this->sembrarDatosBasePrueba();
        $ahora = Carbon::now();

        InscripcionSysteme::create([
            'id_systeme_inscripcion'    => '24540790',
            'id_systeme_curso'          => '674252',
            'curso_nombre_systeme'      => 'Evolución Consciente',
            'id_systeme_contacto'       => '447325273',
            'email_contacto'            => 'alumno9@ejemplo.cl',
            'access_type'               => 'full_access',
            'systeme_activo'            => true,
            'programa_id'               => $evolucion->id,
            'persona_id'                => null,
            'vinculacion_estado'        => InscripcionSysteme::VINCULACION_PENDIENTE,
            'vinculacion_motivo_codigo' => InscripcionSysteme::MOTIVO_PERSONA_NO_ENCONTRADA,
            'primera_sincronizacion_en' => $ahora,
            'sincronizado_en'           => $ahora,
        ]);

        Http::fake([
            'https://api.systeme.io/api/contacts/447325273' => Http::response([
                'id'     => 447325273,
                'email'  => 'alumno9@ejemplo.cl',
                'fields' => [
                    ['slug' => 'first_name', 'value' => 'María'],
                    ['slug' => 'surname', 'value' => 'González'],
                ],
            ], 200),
        ]);

        // Invitado -> login
        $this->post('/inscripciones/importar-alumnos')->assertRedirect('/login');

        // Facilitador activo -> 403
        $facilitador = User::factory()->create(['is_active' => true, 'email' => 'fac@neuromanagement.cl']);
        $this->asignarRol($facilitador, UsuarioRol::ROL_FACILITADOR);
        $this->actingAs($facilitador)->post('/inscripciones/importar-alumnos')->assertForbidden();

        // Administrador inactivo -> 403
        $adminInactivo = User::factory()->create(['is_active' => false, 'email' => 'inactivo2@neuromanagement.cl']);
        $this->asignarRol($adminInactivo, UsuarioRol::ROL_ADMINISTRADOR);
        $this->actingAs($adminInactivo)->post('/inscripciones/importar-alumnos')->assertForbidden();

        // Administrador activo -> ejecuta la importación y muestra el resumen en la bandeja
        $admin = User::factory()->create(['is_active' => true, 'email' => 'admin@neuromanagement.cl']);
        $this->asignarRol($admin, UsuarioRol::ROL_ADMINISTRADOR);

        $response = $this->actingAs($admin)
            ->followingRedirects()
            ->post('/inscripciones/importar-alumnos', ['curso' => '674252']);

        $response->assertOk()
            ->assertSee('Importación de alumnos pendientes completada')
            ->assertSee('Personas creadas')
            ->assertSee('Vinculaciones existentes')
            ->assertSee('María González');
    }
}

