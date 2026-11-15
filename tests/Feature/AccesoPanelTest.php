<?php

namespace Tests\Feature;

use App\Models\Persona;
use App\Models\Programa;
use App\Models\User;
use App\Models\Usuario;
use App\Models\UsuarioRol;
use App\Support\AdminNavigation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Control de acceso del panel: permisos de lectura de Personas y Programas
 * y bloqueo del inicio de sesión para cuentas inactivas.
 */
class AccesoPanelTest extends TestCase
{
    private Persona $persona;

    private Programa $programa;

    protected function setUp(): void
    {
        // TestCase::setUp() verifica SQLite en memoria antes de crear el esquema.
        parent::setUp();

        $this->crearEsquemaDePruebasEnMemoria();

        $this->persona = Persona::create([
            'nombres'     => 'Ana',
            'apellidos'   => 'Pérez',
            'email'       => 'ana.perez@ejemplo.cl',
            'origen_lead' => 'systeme.io',
            'estado'      => 'alumno_activo',
            'creado_en'   => now(),
        ]);

        $this->programa = Programa::create([
            'nombre'            => 'Evolución Consciente',
            'id_systeme_curso'  => '674252',
            'systeme_activo'    => true,
            'systeme_presente'  => true,
            'estructura_estado' => Programa::ESTRUCTURA_COMPLETA,
        ]);
    }

    private function crearEsquemaDePruebasEnMemoria(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('persona', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('tipo_documento')->nullable();
            $table->string('numero_documento')->nullable();
            $table->string('pais_documento')->nullable();
            $table->string('email');
            $table->string('telefono')->nullable();
            $table->string('pais')->nullable();
            $table->string('region')->nullable();
            $table->string('ciudad')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero')->nullable();
            $table->string('nivel_educacional')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('origen_lead');
            $table->string('estado');
            $table->string('id_systeme_contacto')->nullable()->unique();
            $table->dateTime('creado_en');
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

        Schema::create('modulo', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('programa_id');
            $table->string('titulo');
            $table->integer('orden');
            $table->string('id_systeme_modulo')->nullable()->unique();
            $table->boolean('systeme_presente')->nullable();
        });

        Schema::create('leccion', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('modulo_id');
            $table->string('titulo');
            $table->integer('orden');
            $table->integer('duracion_min')->nullable();
            $table->bigInteger('competencia_id')->nullable();
            $table->string('id_systeme_leccion')->nullable()->unique();
            $table->boolean('systeme_activo')->nullable();
            $table->boolean('systeme_presente')->nullable();
        });

        // Tablas que carga la ficha de la persona (personas.show).
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
            $table->string('estado')->default('inscrito');
            $table->decimal('avance_pct', 5, 2)->nullable();
            $table->decimal('nota_final', 3, 2)->nullable();
            $table->string('id_systeme_inscripcion')->nullable()->unique();
        });

        Schema::create('v_asistencia_inscripcion', function (Blueprint $table) {
            $table->bigInteger('inscripcion_id')->primary();
            $table->decimal('asistencia_pct', 5, 2)->nullable();
        });

        Schema::create('usuario', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('persona_id')->nullable()->unique();
            $table->string('proveedor_auth');
            $table->string('sujeto_auth');
            $table->boolean('activo')->default(true);
            $table->unique(['proveedor_auth', 'sujeto_auth']);
        });

        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('usuario_id');
            $table->string('rol');
            $table->bigInteger('empresa_id')->nullable();
            $table->bigInteger('empresa_scope')->default(0);
        });
    }

    private function crearCuenta(bool $cuentaActiva = true, string $password = 'clave-de-prueba'): User
    {
        return User::factory()->create([
            'is_active' => $cuentaActiva,
            'password'  => Hash::make($password),
        ]);
    }

    private function asignarRol(User $user, string $rol, bool $usuarioActivo = true, ?int $empresaId = null): void
    {
        $usuario = Usuario::create([
            'proveedor_auth' => Usuario::PROVEEDOR_LOCAL,
            'sujeto_auth'    => (string) $user->id,
            'activo'         => $usuarioActivo,
        ]);

        UsuarioRol::create([
            'usuario_id' => $usuario->id,
            'rol'        => $rol,
            'empresa_id' => $empresaId,
        ]);
    }

    /** @return list<string> */
    private function rutasPersonas(): array
    {
        return ['/personas', "/personas/{$this->persona->id}"];
    }

    /** @return list<string> */
    private function rutasProgramas(): array
    {
        return ['/programas', "/programas/{$this->programa->id}"];
    }

    /** @return list<string> */
    private function etiquetasMenu(): array
    {
        return collect(AdminNavigation::sections())
            ->flatMap(fn ($s) => $s['items'])
            ->pluck('label')
            ->all();
    }

    private function assertSinAccesoAModulos(User $user): void
    {
        foreach ([...$this->rutasPersonas(), ...$this->rutasProgramas()] as $ruta) {
            $this->actingAs($user)->get($ruta)->assertForbidden();
        }
    }

    // ---------------------------------------------------------------------
    // Permisos de lectura de Personas y Programas
    // ---------------------------------------------------------------------

    public function test_invitado_es_redirigido_al_login(): void
    {
        foreach ([...$this->rutasPersonas(), ...$this->rutasProgramas()] as $ruta) {
            $this->get($ruta)->assertRedirect('/login');
        }
    }

    public function test_cuenta_activa_sin_rol_recibe_403(): void
    {
        $this->assertSinAccesoAModulos($this->crearCuenta());
    }

    public function test_administrador_activo_puede_abrir_personas_y_programas(): void
    {
        $admin = $this->crearCuenta();
        $this->asignarRol($admin, UsuarioRol::ROL_ADMINISTRADOR);

        $this->actingAs($admin)->get('/personas')->assertOk()->assertSee('Ana');
        $this->actingAs($admin)->get("/personas/{$this->persona->id}")->assertOk()->assertSee('Pérez');
        $this->actingAs($admin)->get('/programas')->assertOk()->assertSee('Evolución Consciente');
        $this->actingAs($admin)->get("/programas/{$this->programa->id}")->assertOk()->assertSee('Evolución Consciente');

        $this->actingAs($admin);
        $menu = $this->etiquetasMenu();
        $this->assertContains('Personas / Alumnos', $menu);
        $this->assertContains('Programas / Cursos', $menu);
    }

    public function test_administrador_con_cuenta_inactiva_recibe_403(): void
    {
        $admin = $this->crearCuenta(cuentaActiva: false);
        $this->asignarRol($admin, UsuarioRol::ROL_ADMINISTRADOR);

        $this->assertSinAccesoAModulos($admin);
    }

    public function test_usuario_de_dominio_inactivo_recibe_403_aunque_la_cuenta_este_activa(): void
    {
        $admin = $this->crearCuenta(cuentaActiva: true);
        $this->asignarRol($admin, UsuarioRol::ROL_ADMINISTRADOR, usuarioActivo: false);

        $this->assertSinAccesoAModulos($admin);
    }

    public function test_rol_limitado_a_una_empresa_recibe_403_aunque_la_cuenta_este_activa(): void
    {
        $adminEmpresa = $this->crearCuenta(cuentaActiva: true);
        $this->asignarRol($adminEmpresa, UsuarioRol::ROL_ADMINISTRADOR, empresaId: 1);

        $facilitadorEmpresa = $this->crearCuenta(cuentaActiva: true);
        $this->asignarRol($facilitadorEmpresa, UsuarioRol::ROL_FACILITADOR, empresaId: 1);

        $this->assertSinAccesoAModulos($adminEmpresa);
        $this->assertSinAccesoAModulos($facilitadorEmpresa);
    }

    public function test_facilitador_consulta_programas_pero_no_personas_ni_edita_ni_sincroniza(): void
    {
        $facilitador = $this->crearCuenta();
        $this->asignarRol($facilitador, UsuarioRol::ROL_FACILITADOR);

        // Puede consultar Programas, sin ver las acciones de administración
        $this->actingAs($facilitador)->get('/programas')
            ->assertOk()
            ->assertSee('Evolución Consciente')
            ->assertDontSee('Sincronizar desde Systeme.io');
        $this->actingAs($facilitador)->get("/programas/{$this->programa->id}")
            ->assertOk()
            ->assertDontSee('Guardar información local');

        // No puede ver Personas
        foreach ($this->rutasPersonas() as $ruta) {
            $this->actingAs($facilitador)->get($ruta)->assertForbidden();
        }

        // No puede editar ni sincronizar (sin peticiones a Systeme.io: preventStrayRequests está activo)
        $this->actingAs($facilitador)
            ->patch("/programas/{$this->programa->id}", ['linea' => 'abierto'])
            ->assertForbidden();
        $this->actingAs($facilitador)->post('/programas/sincronizar')->assertForbidden();

        $this->assertNull($this->programa->fresh()->linea);

        // Menú: ve Programas pero no Personas
        $this->actingAs($facilitador);
        $menu = $this->etiquetasMenu();
        $this->assertContains('Programas / Cursos', $menu);
        $this->assertNotContains('Personas / Alumnos', $menu);
    }

    // ---------------------------------------------------------------------
    // Inicio de sesión
    // ---------------------------------------------------------------------

    public function test_cuenta_inactiva_no_puede_iniciar_sesion_y_recibe_el_mensaje_generico(): void
    {
        $inactiva = $this->crearCuenta(cuentaActiva: false, password: 'clave-correcta');

        // Credenciales correctas de una cuenta inactiva
        $this->from('/login')
            ->post('/login', ['email' => $inactiva->email, 'password' => 'clave-correcta'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Las credenciales ingresadas no son correctas.']);
        $this->assertGuest();

        // Mismo mensaje que con una contraseña incorrecta
        $this->from('/login')
            ->post('/login', ['email' => $inactiva->email, 'password' => 'clave-incorrecta'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Las credenciales ingresadas no son correctas.']);
        $this->assertGuest();
    }

    public function test_cuenta_activa_inicia_sesion_y_llega_al_dashboard(): void
    {
        $activa = $this->crearCuenta(cuentaActiva: true, password: 'clave-correcta');

        $this->post('/login', ['email' => $activa->email, 'password' => 'clave-correcta'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($activa);
    }
}
