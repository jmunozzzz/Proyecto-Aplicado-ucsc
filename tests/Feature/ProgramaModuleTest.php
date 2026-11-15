<?php

namespace Tests\Feature;

use App\Models\Leccion;
use App\Models\Modulo;
use App\Models\Persona;
use App\Models\Programa;
use App\Models\User;
use App\Models\Usuario;
use App\Models\UsuarioRol;
use App\Services\Systeme\ProgramaSyncService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProgramaModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Garantía estricta: las pruebas solo corren en SQLite en memoria y nunca tocan MySQL.
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
            $table->id();
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
            $table->unique(['codigo', 'version']);
        });

        Schema::create('modulo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('programa_id');
            $table->string('titulo');
            $table->integer('orden');
            $table->string('id_systeme_modulo')->nullable()->unique();
            $table->boolean('systeme_presente')->nullable();
            $table->unique(['programa_id', 'orden']);
        });

        Schema::create('leccion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('modulo_id');
            $table->string('titulo');
            $table->integer('orden');
            $table->integer('duracion_min')->nullable();
            $table->unsignedBigInteger('competencia_id')->nullable();
            $table->string('id_systeme_leccion')->nullable()->unique();
            $table->boolean('systeme_activo')->nullable();
            $table->boolean('systeme_presente')->nullable();
            $table->unique(['modulo_id', 'orden']);
        });

        Schema::create('persona', function (Blueprint $table) {
            $table->id();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('email')->nullable();
            $table->string('estado')->nullable();
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
    }

    private function asignarRol(User $user, string $rol, bool $usuarioActivo = true): void
    {
        $usuario = Usuario::create([
            'proveedor_auth' => 'local',
            'sujeto_auth'    => (string) $user->id,
            'activo'         => $usuarioActivo,
        ]);

        UsuarioRol::create([
            'usuario_id' => $usuario->id,
            'rol'        => $rol,
        ]);
    }

    /**
     * Genera la respuesta simulada de Evolución Consciente con 13 módulos y 79 lecciones
     * (en orden descendente como devuelve la API de Systeme.io).
     */
    private function payloadEvolucionConsciente(bool $hasMore = false): array
    {
        $cantidadesPorPosicion = [
            1 => [7, true],
            2 => [6, false],
            3 => [6, false],
            4 => [6, false],
            5 => [6, false],
            6 => [8, true],
            7 => [6, true],
            8 => [8, true],
            9 => [6, true],
            10 => [4, false],
            11 => [5, false],
            12 => [6, false],
            13 => [5, false],
        ]; // Suma = 79 lecciones en 13 módulos

        $listModules = [];
        $structureModules = [];
        $lectureIdSeq = 11000000;

        foreach (array_reverse($cantidadesPorPosicion, true) as $modPos => [$numLectures, $activeFlag]) {
            $modId = 2700000 + $modPos;
            $listModules[] = ['id' => $modId, 'name' => "Módulo {$modPos}"];

            $lectures = [];
            for ($lPos = $numLectures; $lPos >= 1; $lPos--) {
                $lectureIdSeq++;
                $lectures[] = [
                    'id'                         => $lectureIdSeq,
                    'name'                       => "Lección {$modPos}.{$lPos}",
                    'active'                     => $activeFlag,
                    'position'                   => $lPos,
                    'delayBeforePreviousLecture' => 7,
                ];
            }

            $structureModules[] = [
                'id'       => $modId,
                'name'     => "Módulo {$modPos}",
                'position' => $modPos,
                'lectures' => $lectures,
            ];
        }

        return [
            'list_item' => [
                'id'          => 674252,
                'locale'      => 'es',
                'domainName'  => 'jorgevargas.systeme.io',
                'path'        => 'evolucionconsciente',
                'active'      => true,
                'name'        => 'Evolución Consciente',
                'description' => 'Descripción oficial del programa',
                'modules'     => array_reverse($listModules),
            ],
            'detail' => [
                'id'              => 674252,
                'locale'          => 'es',
                'path'            => 'evolucionconsciente',
                'active'          => true,
                'name'            => 'Evolución Consciente',
                'description'     => 'Descripción oficial del programa',
                'instructorName'  => 'JORGE VARGAS FARA',
                'createdAt'       => '2026-09-14T18:00:56+00:00',
                'image'           => ['dataFileId' => 1, 'url' => 'https://example.com/cover.jpg'],
                'courseOwnerUrl'  => 'https://systeme.io/dashboard/user-domain/login-link?token=secreto',
                'shareUrl'        => 'https://systeme.io/course/share/secreto',
                'coursePublicUrl' => 'https://jorgevargas.systeme.io/school/course/evolucionconsciente',
            ],
            'structure' => [
                'items' => [
                    [
                        'courseId'   => 674252,
                        'courseType' => 'classic_course',
                        'modules'    => $structureModules,
                    ],
                ],
                'hasMore' => $hasMore,
            ],
        ];
    }

    public function test_sincronizar_dos_veces_no_duplica_y_conserva_campos_locales(): void
    {
        $ec = $this->payloadEvolucionConsciente(false);

        Http::fake([
            'https://api.systeme.io/api/school/courses?*' => Http::response([
                'items'   => [$ec['list_item']],
                'hasMore' => false,
            ], 200),
            'https://api.systeme.io/api/school/courses/674252' => Http::response($ec['detail'], 200),
            'https://api.systeme.io/api/school/courses/674252/modules-with-lectures*' => Http::response($ec['structure'], 200),
        ]);

        /** @var ProgramaSyncService $service */
        $service = $this->app->make(ProgramaSyncService::class);

        // Primera sincronización
        $res1 = $service->sincronizar();
        $this->assertSame(1, $res1->creados);
        $this->assertSame(0, $res1->actualizados);
        $this->assertSame(0, $res1->fallidos);
        $this->assertSame(1, $res1->estructurasCompletas);
        $this->assertTrue($res1->completa());

        $this->assertSame(1, Programa::count());
        $this->assertSame(13, Modulo::count());
        $this->assertSame(79, Leccion::count());

        $programa = Programa::firstOrFail();
        $this->assertSame('Evolución Consciente', $programa->nombre);
        $this->assertSame('JORGE VARGAS FARA', $programa->instructor);
        $this->assertSame('https://jorgevargas.systeme.io/school/course/evolucionconsciente', $programa->url_publica);
        $this->assertSame(Programa::ESTRUCTURA_COMPLETA, $programa->estructura_estado);
        $this->assertTrue($programa->tieneEstructuraConfirmada());

        // Verificar que módulos y lecciones quedaron ordenados ascendentemente por position
        $ordenesModulos = $programa->modulos()->pluck('orden')->all();
        $this->assertSame(range(1, 13), $ordenesModulos);

        $primerModulo = $programa->modulos()->first();
        $this->assertSame(range(1, 7), $primerModulo->lecciones()->pluck('orden')->all());

        // Completar campos locales en Laravel y en una lección
        $programa->update([
            'linea'      => 'corporativo',
            'modalidad'  => 'mixta',
            'categoria'  => 'NeuroLiderazgo',
            'horas'      => 36.5,
            'objetivos'  => 'Desarrollar liderazgo ejecutivo consciente.',
            'requisitos' => 'Cargo de jefatura o dirección.',
        ]);
        $primeraLeccion = $primerModulo->lecciones()->first();
        $primeraLeccion->update(['duracion_min' => 45]);

        // Segunda sincronización: no duplica y conserva campos locales
        $res2 = $service->sincronizar();
        $this->assertSame(0, $res2->creados);
        $this->assertSame(1, $res2->actualizados);
        $this->assertSame(1, Programa::count());
        $this->assertSame(13, Modulo::count());
        $this->assertSame(79, Leccion::count());

        $programa->refresh();
        $this->assertSame('corporativo', $programa->linea);
        $this->assertSame('mixta', $programa->modalidad);
        $this->assertSame('NeuroLiderazgo', $programa->categoria);
        $this->assertSame('36.50', $programa->horas);
        $this->assertSame('Desarrollar liderazgo ejecutivo consciente.', $programa->objetivos);
        $this->assertSame('Cargo de jefatura o dirección.', $programa->requisitos);
        $this->assertSame(45, $primeraLeccion->fresh()->duracion_min);
    }

    public function test_estructura_con_has_more_true_o_error_conserva_ultima_estructura_valida_y_no_borra_registros(): void
    {
        $ecCompleta = $this->payloadEvolucionConsciente(false);
        $ecParcial = $this->payloadEvolucionConsciente(true);
        // En la parcial dejamos solo 1 módulo para comprobar que NO reemplaza los 13 módulos previos
        $ecParcial['structure']['items'][0]['modules'] = array_slice($ecParcial['structure']['items'][0]['modules'], 0, 1);

        $paso = 1;
        Http::fake(function ($request) use ($ecCompleta, $ecParcial, &$paso) {
            $url = $request->url();
            if (str_contains($url, '/modules-with-lectures')) {
                if ($paso === 1) {
                    return Http::response($ecCompleta['structure'], 200);
                }
                if ($paso === 2) {
                    return Http::response($ecParcial['structure'], 200);
                }

                return Http::response(['detail' => 'Internal error'], 500);
            }
            if (str_ends_with($url, '/courses/674252')) {
                return Http::response($ecCompleta['detail'], 200);
            }

            return Http::response(['items' => [$ecCompleta['list_item']], 'hasMore' => false], 200);
        });

        /** @var ProgramaSyncService $service */
        $service = $this->app->make(ProgramaSyncService::class);

        // Paso 1: sincronización completa (13 módulos, 79 lecciones)
        $service->sincronizar();
        $this->assertSame(13, Modulo::count());
        $this->assertSame(79, Leccion::count());

        // Paso 2: respuesta con hasMore: true -> marca pendiente_paginacion y conserva los 13 módulos y 79 lecciones
        $paso = 2;
        $resPendiente = $service->sincronizar();
        $this->assertSame(1, $resPendiente->estructurasPendientes);
        $this->assertFalse($resPendiente->completa());

        $programa = Programa::firstOrFail();
        $this->assertSame(Programa::ESTRUCTURA_PENDIENTE, $programa->estructura_estado);
        $this->assertSame(ProgramaSyncService::MENSAJE_PENDIENTE, $programa->sincronizacion_mensaje);
        $this->assertTrue($programa->tieneEstructuraConfirmada());
        $this->assertSame(13, Modulo::count());
        $this->assertSame(79, Leccion::count());

        // Paso 3: fallo HTTP 500 al pedir la estructura -> conserva los 13 módulos y 79 lecciones
        $paso = 3;
        $resError = $service->sincronizar();
        $this->assertSame(1, $resError->estructurasConError);
        $programa->refresh();
        $this->assertSame(Programa::ESTRUCTURA_ERROR, $programa->estructura_estado);
        $this->assertSame(13, Modulo::count());
        $this->assertSame(79, Leccion::count());
    }

    public function test_rutas_y_autorizacion_protegen_el_modulo_y_permiten_editar_ficha_local(): void
    {
        // Invitado redirige a login en todas las rutas
        $this->get('/programas')->assertRedirect('/login');
        $this->post('/programas/sincronizar')->assertRedirect('/login');

        $programa = Programa::create([
            'nombre'                   => 'Evolución Consciente',
            'id_systeme_curso'         => '674252',
            'systeme_activo'           => true,
            'systeme_presente'         => true,
            'estructura_estado'        => Programa::ESTRUCTURA_COMPLETA,
            'estructura_confirmada_en' => now(),
        ]);

        // Administrador inactivo: no autorizado para sincronizar ni editar
        $inactivo = User::factory()->create(['is_active' => false]);
        $this->asignarRol($inactivo, UsuarioRol::ROL_ADMINISTRADOR);
        $this->actingAs($inactivo)->post('/programas/sincronizar')->assertForbidden();
        $this->actingAs($inactivo)->patch("/programas/{$programa->id}", ['linea' => 'abierto'])->assertForbidden();

        // Administrador activo (con rol administrador_nm): puede ver listado, detalle y actualizar ficha local
        $admin = User::factory()->create(['is_active' => true]);
        $this->asignarRol($admin, UsuarioRol::ROL_ADMINISTRADOR);

        $this->actingAs($admin)
            ->get('/programas')
            ->assertOk()
            ->assertSee('Evolución Consciente')
            ->assertSee('Sincronizar desde Systeme.io');

        $this->actingAs($admin)
            ->patch("/programas/{$programa->id}", [
                'linea'      => 'abierto',
                'modalidad'  => 'asincronica',
                'categoria'  => 'Ejecutivo',
                'horas'      => '24',
                'objetivos'  => 'Objetivo 1',
                'requisitos' => 'Requisito 1',
            ])
            ->assertRedirect("/programas/{$programa->id}");

        $programa->refresh();
        $this->assertSame('abierto', $programa->linea);
        $this->assertSame('asincronica', $programa->modalidad);
        $this->assertSame('Ejecutivo', $programa->categoria);
        $this->assertSame('24.00', $programa->horas);
        $this->assertSame('Objetivo 1', $programa->objetivos);
        $this->assertSame('Requisito 1', $programa->requisitos);
    }

    public function test_usuario_activo_sin_privilegios_recibe_403_al_intentar_editar_y_sincronizar_y_no_ve_acciones(): void
    {
        $programa = Programa::create([
            'nombre'                   => 'Evolución Consciente',
            'id_systeme_curso'         => '674252',
            'linea'                    => 'corporativo',
            'systeme_activo'           => true,
            'systeme_presente'         => true,
            'estructura_estado'        => Programa::ESTRUCTURA_COMPLETA,
            'estructura_confirmada_en' => now(),
        ]);

        // Caso 1: Usuario activo con rol no administrador (facilitador)
        $usuarioFacilitador = User::factory()->create(['is_active' => true, 'email' => 'facilitador@neuromanagement.cl']);
        $this->asignarRol($usuarioFacilitador, UsuarioRol::ROL_FACILITADOR);

        // Puede consultar listado y detalle, pero las acciones de sincronizar y guardar están ocultas
        $this->actingAs($usuarioFacilitador)
            ->get('/programas')
            ->assertOk()
            ->assertDontSee('Sincronizar desde Systeme.io');

        $this->actingAs($usuarioFacilitador)
            ->get("/programas/{$programa->id}")
            ->assertOk()
            ->assertDontSee('Guardar información local');

        // Solicitudes directas a sincronizar y editar responden HTTP 403
        $this->actingAs($usuarioFacilitador)
            ->post('/programas/sincronizar')
            ->assertForbidden();

        $this->actingAs($usuarioFacilitador)
            ->patch("/programas/{$programa->id}", ['linea' => 'abierto'])
            ->assertForbidden();

        // Caso 2: Usuario activo sin ningún rol asignado
        $usuarioSinRol = User::factory()->create(['is_active' => true, 'email' => 'sinrol@neuromanagement.cl']);

        $this->actingAs($usuarioSinRol)
            ->post('/programas/sincronizar')
            ->assertForbidden();

        $this->actingAs($usuarioSinRol)
            ->patch("/programas/{$programa->id}", ['linea' => 'abierto'])
            ->assertForbidden();

        // El programa no fue modificado por ninguno de los intentos no autorizados
        $this->assertSame('corporativo', $programa->fresh()->linea);
    }

    public function test_curso_con_modulos_vacios_se_completa_mediante_course_modules(): void
    {
        Http::fake([
            'https://api.systeme.io/api/school/courses?*' => Http::response([
                'items' => [[
                    'id'          => 674153,
                    'locale'      => 'es',
                    'path'        => 'claridadejecutiva',
                    'active'      => false,
                    'name'        => 'Claridad Ejecutiva',
                    'description' => 'Curso ejecutivo',
                    'modules'     => [
                        ['id' => 2742965, 'name' => 'Módulo 1'],
                        ['id' => 2742966, 'name' => 'Módulo 2 vacío'],
                    ],
                ]],
                'hasMore' => false,
            ], 200),
            'https://api.systeme.io/api/school/courses/674153' => Http::response([
                'id'     => 674153,
                'name'   => 'Claridad Ejecutiva',
                'active' => false,
            ], 200),
            'https://api.systeme.io/api/school/courses/674153/modules-with-lectures*' => Http::response([
                'items' => [[
                    'courseId'   => 674153,
                    'courseType' => 'classic_course',
                    'modules'    => [[
                        'id'       => 2742965,
                        'name'     => 'Módulo 1',
                        'position' => 1,
                        'lectures' => [[
                            'id'       => 10981892,
                            'name'     => 'Lección 1.1',
                            'active'   => false,
                            'position' => 1,
                        ]],
                    ]],
                ]],
                'hasMore' => false,
            ], 200),
            'https://api.systeme.io/api/school/course-modules*' => Http::response([
                'items' => [
                    ['id' => 2742966, 'courseId' => 674153, 'name' => 'Módulo 2 vacío', 'position' => 2],
                    ['id' => 2742965, 'courseId' => 674153, 'name' => 'Módulo 1', 'position' => 1],
                ],
                'hasMore' => false,
            ], 200),
        ]);

        /** @var ProgramaSyncService $service */
        $service = $this->app->make(ProgramaSyncService::class);
        $res = $service->sincronizar();

        $this->assertTrue($res->completa());
        $this->assertSame(1, Programa::count());
        $this->assertSame(2, Modulo::count());
        $this->assertSame(1, Leccion::count());
    }

    public function test_correo_del_administrador_por_si_solo_no_concede_privilegios(): void
    {
        $programa = Programa::create([
            'nombre'           => 'Evolución Consciente',
            'id_systeme_curso' => '674252',
            'linea'            => 'corporativo',
        ]);

        // Cuenta activa con el correo ADMIN_EMAIL ('admin@neuromanagement.cl'), pero sin registro en usuario / usuario_rol
        $userConCorreoAdminSinRol = User::factory()->create([
            'email'     => 'admin@neuromanagement.cl',
            'is_active' => true,
        ]);

        $this->assertNull($userConCorreoAdminSinRol->usuarioDominio());
        $this->assertFalse($userConCorreoAdminSinRol->esAdministradorActivo());

        $this->actingAs($userConCorreoAdminSinRol)
            ->post('/programas/sincronizar')
            ->assertForbidden();

        $this->actingAs($userConCorreoAdminSinRol)
            ->patch("/programas/{$programa->id}", ['linea' => 'abierto'])
            ->assertForbidden();

        $this->assertSame('corporativo', $programa->fresh()->linea);
    }

    public function test_usuario_dominio_deniega_permiso_si_id_sujeto_auth_y_correo_apuntan_a_registros_distintos(): void
    {
        $programa = Programa::create([
            'nombre'           => 'Evolución Consciente',
            'id_systeme_curso' => '674252',
            'linea'            => 'corporativo',
        ]);

        $user = User::factory()->create([
            'email'     => 'conflicto@neuromanagement.cl',
            'is_active' => true,
        ]);

        // Registro Usuario #1 coincide por ID en sujeto_auth y tiene rol administrador_nm
        $usuarioPorId = Usuario::create([
            'proveedor_auth' => 'local',
            'sujeto_auth'    => (string) $user->id,
            'activo'         => true,
        ]);
        UsuarioRol::create([
            'usuario_id' => $usuarioPorId->id,
            'rol'        => UsuarioRol::ROL_ADMINISTRADOR,
        ]);

        // Registro Usuario #2 distinto coincide por correo (sujeto_auth = email)
        Usuario::create([
            'proveedor_auth' => 'email',
            'sujeto_auth'    => 'conflicto@neuromanagement.cl',
            'activo'         => true,
        ]);

        // Al apuntar a dos registros distintos en `usuario`, debe denegar el permiso
        $this->assertNull($user->usuarioDominio());
        $this->assertFalse($user->esAdministradorActivo());

        $this->actingAs($user)
            ->post('/programas/sincronizar')
            ->assertForbidden();

        $this->actingAs($user)
            ->patch("/programas/{$programa->id}", ['linea' => 'abierto'])
            ->assertForbidden();

        // Si además existe conflicto entre el Usuario hallado por ID y una Persona con el mismo correo no vinculada
        Usuario::where('proveedor_auth', 'email')->delete();
        Persona::create([
            'nombres'   => 'Otra',
            'apellidos' => 'Persona',
            'email'     => 'conflicto@neuromanagement.cl',
        ]);

        $this->assertNull($user->usuarioDominio());
        $this->assertFalse($user->esAdministradorActivo());
    }
}
