<?php

namespace App\Services\Systeme;

use App\Models\InscripcionSysteme;
use App\Models\Persona;
use App\Models\Programa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sincronización unidireccional Systeme.io → Laravel de la bandeja de inscripciones
 * (`GET /api/school/enrollments` → tabla `inscripcion_systeme`).
 *
 * Reglas:
 *  - Consulta `GET /api/school/enrollments` con `limit=100` y `order=desc`.
 *  - Cuando `hasMore=true`, usa como `startingAfter` el último ID de la página y
 *    comprueba que disminuya estrictamente en cada ítem y entre páginas.
 *  - Si falla una página o el cursor se repite / no decrece, informa que la
 *    sincronización quedó incompleta y conserva lo ya procesado.
 *  - La ausencia de un registro en una respuesta nunca lo elimina ni modifica
 *    su estado de acceso (`systeme_activo`) anterior.
 *  - Solo escribe en `inscripcion_systeme` (no crea registros en `persona`,
 *    `cohorte`, `consentimiento` ni `inscripcion`).
 */
class InscripcionSyncService
{
    public const MENSAJE_PAGINACION_INCOMPLETA = 'Sincronización incompleta: no se pudieron recorrer todas las páginas de inscripciones.';

    /** Curso inicial por defecto para importar alumnos pendientes: Evolución Consciente. */
    public const CURSO_DEFAULT_SYSTEME_ID = '674252';
    public const CURSO_DEFAULT_NOMBRE     = 'Evolución Consciente';

    /** Límite defensivo de páginas por ejecución para evitar bucles infinitos. */
    private const MAX_PAGINAS = 200;

    public function __construct(
        private SystemeClient $client,
        private SystemeEnrollmentParser $parser,
    ) {}

    public function sincronizar(): InscripcionSyncResult
    {
        $result = new InscripcionSyncResult();

        $lock = Cache::lock('systeme:sync-inscripciones', 600);
        if (! $lock->get()) {
            $result->listadoCompleto = false;
            $result->errorGeneral = 'Ya hay una sincronización de inscripciones en curso. Inténtalo de nuevo en unos minutos.';

            return $result;
        }

        try {
            $this->ejecutar($result);
        } finally {
            $lock->release();
        }

        return $result;
    }

    /**
     * Procesa las inscripciones pendientes del curso seleccionado (por defecto
     * Evolución Consciente, ID #674252) consultando `GET /api/contacts/{id}`
     * una sola vez por contacto distinto y creando o vinculando fichas `Persona`.
     */
    public function importarAlumnosPendientes(?string $curso = null): ImportarAlumnosResult
    {
        $result = new ImportarAlumnosResult();

        $cursoFiltro = trim((string) $curso);
        if ($cursoFiltro === '') {
            $cursoFiltro = self::CURSO_DEFAULT_SYSTEME_ID;
        }
        $result->cursoFiltro = $cursoFiltro;

        $lock = Cache::lock('systeme:import-alumnos-inscripciones', 600);
        if (! $lock->get()) {
            $result->errorGeneral = 'Ya hay una importación de alumnos pendientes en curso. Inténtalo de nuevo en unos minutos.';

            return $result;
        }

        try {
            $this->ejecutarImportacionAlumnos($cursoFiltro, $result);
        } finally {
            $lock->release();
        }

        return $result;
    }

    private function ejecutarImportacionAlumnos(string $cursoFiltro, ImportarAlumnosResult $result): void
    {
        $programa = null;
        if ($cursoFiltro !== 'sin_curso' && $cursoFiltro !== 'todos') {
            $programa = Programa::query()
                ->where('id_systeme_curso', $cursoFiltro)
                ->when(ctype_digit($cursoFiltro), fn ($q) => $q->orWhere('id', (int) $cursoFiltro))
                ->first();
        }

        if ($programa !== null) {
            $result->cursoNombre = $programa->nombre;
        } elseif ($cursoFiltro === self::CURSO_DEFAULT_SYSTEME_ID) {
            $result->cursoNombre = self::CURSO_DEFAULT_NOMBRE;
        }

        $query = InscripcionSysteme::query()->whereNull('persona_id');

        if ($cursoFiltro === 'sin_curso') {
            $query->whereNull('programa_id');
        } elseif ($cursoFiltro !== 'todos') {
            $query->where(function ($q) use ($cursoFiltro, $programa) {
                $q->where('id_systeme_curso', $cursoFiltro);
                if ($programa !== null) {
                    $q->orWhere('programa_id', $programa->id);
                    if ($programa->id_systeme_curso !== null && $programa->id_systeme_curso !== '') {
                        $q->orWhere('id_systeme_curso', $programa->id_systeme_curso);
                    }
                } elseif (ctype_digit($cursoFiltro)) {
                    $q->orWhere('programa_id', (int) $cursoFiltro);
                }
            });
        }

        $inscripcionesPendientes = $query
            ->orderByDesc('id_systeme_inscripcion')
            ->orderBy('id')
            ->get();

        if ($result->cursoNombre === null && $inscripcionesPendientes->isNotEmpty()) {
            $result->cursoNombre = $inscripcionesPendientes->first()?->curso_nombre_systeme;
        }

        /**
         * Caché por ejecución indexada por `id_systeme_contacto` para garantizar
         * que `GET /api/contacts/{id}` se consulte una sola vez por contacto distinto.
         *
         * @var array<string, array{ok: bool, data?: array{id: string, email: ?string, nombres: ?string, apellidos: ?string, completo: bool, faltantes: list<string>}, error?: string}>
         */
        $contactosCache = [];

        foreach ($inscripcionesPendientes as $inscripcionPendiente) {
            $result->inscripcionesEvaluadas++;
            $contactoId = trim((string) $inscripcionPendiente->id_systeme_contacto);

            $contactoEntry = $this->obtenerContactoUnicoPorEjecucion($contactoId, $contactosCache, $result);
            if (! $contactoEntry['ok']) {
                $result->fallidos++;
                $result->aviso(
                    "Inscripción #{$inscripcionPendiente->id_systeme_inscripcion} (Contacto #{$contactoId})",
                    'error',
                    $contactoEntry['error'] ?? 'No se pudo consultar el contacto en Systeme.io.'
                );

                continue;
            }

            $datosContacto = $contactoEntry['data'];

            try {
                $outcome = DB::transaction(function () use ($inscripcionPendiente, $contactoId, $datosContacto) {
                    $registro = InscripcionSysteme::query()
                        ->whereKey($inscripcionPendiente->id)
                        ->lockForUpdate()
                        ->first();

                    if ($registro === null || $registro->persona_id !== null) {
                        return ['status' => 'skipped'];
                    }

                    $programaLocal = $registro->programa_id !== null
                        ? Programa::query()->find($registro->programa_id)
                        : Programa::query()->where('id_systeme_curso', $registro->id_systeme_curso)->first();
                    $programaId = $programaLocal?->id;

                    $emailParaBuscar = $datosContacto['email']
                        ?? strtolower(trim((string) $registro->email_contacto));

                    [$personaId, $viaPersona, $motivoCodigoPersona, $motivoPersona] = $this->resolverPersona(
                        $contactoId,
                        $emailParaBuscar,
                        $registro->id
                    );

                    // 1. Conflicto de vinculación: dejar la fila para revisión sin modificar Persona
                    if (in_array($motivoCodigoPersona, [
                        InscripcionSysteme::MOTIVO_CORREO_DUPLICADO,
                        InscripcionSysteme::MOTIVO_CORREO_OTRO_CONTACTO,
                    ], true)) {
                        [$estadoVinculacion, $motivoCodigo, $motivoTexto] = $this->determinarEstadoVinculacion(
                            $programaId,
                            $registro->id_systeme_curso,
                            null,
                            $motivoCodigoPersona,
                            $motivoPersona
                        );

                        $registro->forceFill([
                            'programa_id'               => $programaId,
                            'persona_id'                => null,
                            'vinculacion_estado'        => $estadoVinculacion,
                            'persona_vinculacion_via'   => null,
                            'vinculacion_motivo_codigo' => $motivoCodigo,
                            'vinculacion_motivo'        => $motivoTexto,
                        ]);
                        $registro->save();

                        return [
                            'status'     => 'conflicto',
                            'referencia' => "Inscripción #{$registro->id_systeme_inscripcion}",
                            'mensaje'    => $motivoTexto,
                        ];
                    }

                    // 2. La persona ya existe: vincular sin crear duplicados ni sobrescribir sus datos locales
                    if ($personaId !== null) {
                        [$estadoVinculacion, $motivoCodigo, $motivoTexto] = $this->determinarEstadoVinculacion(
                            $programaId,
                            $registro->id_systeme_curso,
                            $personaId,
                            null,
                            null
                        );

                        $registro->forceFill([
                            'programa_id'               => $programaId,
                            'persona_id'                => $personaId,
                            'vinculacion_estado'        => $estadoVinculacion,
                            'persona_vinculacion_via'   => $viaPersona,
                            'vinculacion_motivo_codigo' => $motivoCodigo,
                            'vinculacion_motivo'        => $motivoTexto,
                        ]);
                        $registro->save();

                        return ['status' => 'vinculada_existente'];
                    }

                    // 3. La persona no existe: verificar datos obligatorios (correo, nombre y apellido)
                    if (! $datosContacto['completo']) {
                        $faltantes = $datosContacto['faltantes'];
                        $listaFaltantes = implode(', ', $faltantes);
                        $prefijo = count($faltantes) === 1
                            ? 'falta el campo obligatorio'
                            : 'faltan los campos obligatorios';
                        $motivoIncompleto = "Contacto #{$contactoId} con datos incompletos en Systeme.io: {$prefijo} {$listaFaltantes}.";

                        [$estadoVinculacion, $motivoCodigo, $motivoTexto] = $this->determinarEstadoVinculacion(
                            $programaId,
                            $registro->id_systeme_curso,
                            null,
                            InscripcionSysteme::MOTIVO_DATOS_INCOMPLETOS,
                            $motivoIncompleto
                        );

                        $registro->forceFill([
                            'programa_id'               => $programaId,
                            'persona_id'                => null,
                            'vinculacion_estado'        => $estadoVinculacion,
                            'persona_vinculacion_via'   => null,
                            'vinculacion_motivo_codigo' => $motivoCodigo,
                            'vinculacion_motivo'        => $motivoTexto,
                        ]);
                        $registro->save();

                        return [
                            'status'     => 'pendiente',
                            'referencia' => "Inscripción #{$registro->id_systeme_inscripcion}",
                            'mensaje'    => $motivoTexto,
                        ];
                    }

                    // 4. Contacto completo y sin conflictos: crear ficha en `persona` y vincular en la misma transacción
                    $nuevaPersona = Persona::query()->create([
                        'nombres'             => $datosContacto['nombres'],
                        'apellidos'           => $datosContacto['apellidos'],
                        'email'               => $datosContacto['email'],
                        'id_systeme_contacto' => $contactoId,
                        'origen_lead'         => 'systeme.io',
                        'estado'              => 'alumno_activo',
                        'creado_en'           => Carbon::now(),
                    ]);

                    [$estadoVinculacion, $motivoCodigo, $motivoTexto] = $this->determinarEstadoVinculacion(
                        $programaId,
                        $registro->id_systeme_curso,
                        (int) $nuevaPersona->id,
                        null,
                        null
                    );

                    $registro->forceFill([
                        'email_contacto'            => $datosContacto['email'],
                        'programa_id'               => $programaId,
                        'persona_id'                => (int) $nuevaPersona->id,
                        'vinculacion_estado'        => $estadoVinculacion,
                        'persona_vinculacion_via'   => InscripcionSysteme::VIA_ID_CONTACTO,
                        'vinculacion_motivo_codigo' => $motivoCodigo,
                        'vinculacion_motivo'        => $motivoTexto,
                    ]);
                    $registro->save();

                    return ['status' => 'persona_creada'];
                });
            } catch (Throwable $e) {
                $result->fallidos++;
                $result->aviso(
                    "Inscripción #{$inscripcionPendiente->id_systeme_inscripcion}",
                    'error',
                    'No se pudo procesar la importación del alumno en la base de datos local.'
                );
                Log::warning('Importación de alumnos pendientes: error en transacción', [
                    'inscripcion_systeme' => $inscripcionPendiente->id_systeme_inscripcion,
                    'contacto_systeme'    => $contactoId,
                    'causa'               => class_basename($e),
                ]);

                continue;
            }

            switch ($outcome['status']) {
                case 'persona_creada':
                    $result->personasCreadas++;
                    break;
                case 'vinculada_existente':
                    $result->vinculadasExistentes++;
                    break;
                case 'pendiente':
                    $result->pendientes++;
                    if (! empty($outcome['mensaje'])) {
                        $result->aviso($outcome['referencia'], 'pendiente', $outcome['mensaje']);
                    }
                    break;
                case 'conflicto':
                    $result->conflictos++;
                    if (! empty($outcome['mensaje'])) {
                        $result->aviso($outcome['referencia'], 'conflicto', $outcome['mensaje']);
                    }
                    break;
            }
        }
    }

    /**
     * Consulta `GET /api/contacts/{id}` a lo sumo una vez por contacto distinto en la ejecución.
     *
     * @param  array<string, array{ok: bool, data?: array{id: string, email: ?string, nombres: ?string, apellidos: ?string, completo: bool, faltantes: list<string>}, error?: string}>  $contactosCache
     * @return array{ok: bool, data?: array{id: string, email: ?string, nombres: ?string, apellidos: ?string, completo: bool, faltantes: list<string>}, error?: string}
     */
    private function obtenerContactoUnicoPorEjecucion(
        string $contactoId,
        array &$contactosCache,
        ImportarAlumnosResult $result,
    ): array {
        if (isset($contactosCache[$contactoId])) {
            return $contactosCache[$contactoId];
        }

        $result->contactosConsultados++;

        try {
            $rawContact = $this->client->getContact($contactoId);
            $parsed = $this->parser->contact($rawContact, $contactoId);

            return $contactosCache[$contactoId] = [
                'ok'   => true,
                'data' => $parsed,
            ];
        } catch (SystemeException $e) {
            Log::warning('Importación de alumnos pendientes: fallo al consultar contacto en Systeme.io', [
                'contacto_systeme' => $contactoId,
                'estado_http'      => $e->status,
            ]);

            return $contactosCache[$contactoId] = [
                'ok'    => false,
                'error' => "No se pudo obtener el contacto #{$contactoId} desde Systeme.io ({$e->getMessage()}).",
            ];
        }
    }

    private function ejecutar(InscripcionSyncResult $result): void
    {
        $startingAfter = null;
        $ultimoIdVisto = null;
        $idsVistos = [];

        for ($paginaNum = 1; $paginaNum <= self::MAX_PAGINAS; $paginaNum++) {
            try {
                $pagina = $this->client->listEnrollments($startingAfter, SystemeClient::MAX_LIMIT);
            } catch (SystemeException $e) {
                $result->listadoCompleto = false;
                if ($result->paginasProcesadas === 0) {
                    $result->errorGeneral = 'Sincronización incompleta: no se pudo obtener el listado de inscripciones (' . $e->getMessage() . ').';
                } else {
                    $result->aviso(
                        "Página {$paginaNum}",
                        'error',
                        "Sincronización incompleta: falló la consulta de la página {$paginaNum} ({$e->getMessage()}). Se conservaron las páginas anteriores."
                    );
                }
                Log::warning('Sincronización de inscripciones: fallo al consultar página', [
                    'pagina'         => $paginaNum,
                    'starting_after' => $startingAfter,
                    'estado_http'    => $e->status,
                ]);

                return;
            }

            $items = $pagina['items'];
            $hasMore = $pagina['hasMore'];

            if ($items === []) {
                if ($hasMore) {
                    $result->listadoCompleto = false;
                    $result->aviso(
                        "Página {$paginaNum}",
                        'error',
                        'Sincronización incompleta: Systeme.io indicó más páginas (hasMore=true) pero devolvió una página vacía.'
                    );
                    Log::warning('Sincronización de inscripciones: página vacía con hasMore=true', [
                        'pagina'         => $paginaNum,
                        'starting_after' => $startingAfter,
                    ]);
                } else {
                    $result->paginasProcesadas++;
                }

                return;
            }

            // Verificar antes de procesar la página que los IDs sean enteros positivos,
            // no se repitan y disminuyan estrictamente respecto del cursor anterior y dentro de la página.
            $cursorPrevioControl = $ultimoIdVisto;
            $idsPagina = [];

            foreach ($items as $rawItem) {
                $itemId = is_array($rawItem) ? ($rawItem['id'] ?? null) : null;
                if (! is_int($itemId) || $itemId < 1) {
                    $result->listadoCompleto = false;
                    $result->aviso(
                        "Página {$paginaNum}",
                        'error',
                        'Sincronización incompleta: se recibió una inscripción sin ID entero positivo válido.'
                    );

                    return;
                }

                if (isset($idsVistos[$itemId]) || isset($idsPagina[$itemId])) {
                    $result->listadoCompleto = false;
                    $result->aviso(
                        "Página {$paginaNum}",
                        'error',
                        "Sincronización incompleta: el ID de inscripción #{$itemId} se repitió durante la paginación."
                    );
                    Log::warning('Sincronización de inscripciones: ID repetido en paginación', [
                        'pagina'         => $paginaNum,
                        'inscripcion_id' => $itemId,
                        'starting_after' => $startingAfter,
                    ]);

                    return;
                }

                if ($cursorPrevioControl !== null && $itemId >= $cursorPrevioControl) {
                    $result->listadoCompleto = false;
                    $result->aviso(
                        "Página {$paginaNum}",
                        'error',
                        "Sincronización incompleta: el cursor o ID de inscripción (#{$itemId}) no disminuyó estrictamente respecto del anterior (#{$cursorPrevioControl})."
                    );
                    Log::warning('Sincronización de inscripciones: cursor no decreciente', [
                        'pagina'         => $paginaNum,
                        'id_actual'      => $itemId,
                        'id_previo'      => $cursorPrevioControl,
                        'starting_after' => $startingAfter,
                    ]);

                    return;
                }

                $idsPagina[$itemId] = true;
                $cursorPrevioControl = $itemId;
            }

            // Procesar cada inscripción de la página validada
            foreach ($items as $rawItem) {
                $itemId = (int) $rawItem['id'];
                $idsVistos[$itemId] = true;
                $ultimoIdVisto = $itemId;
                $this->sincronizarItem($rawItem, $result);
            }

            $result->paginasProcesadas++;

            if (! $hasMore) {
                return;
            }

            // Cuando hasMore=true, usamos como startingAfter el último ID de la página
            // y verificamos que disminuya estrictamente respecto del startingAfter anterior.
            if ($ultimoIdVisto === null || ($startingAfter !== null && $ultimoIdVisto >= $startingAfter)) {
                $result->listadoCompleto = false;
                $result->aviso(
                    "Página {$paginaNum}",
                    'error',
                    'Sincronización incompleta: el cursor startingAfter se repitió o no disminuyó estrictamente.'
                );

                return;
            }

            $startingAfter = $ultimoIdVisto;
        }

        // Si se alcanzó el máximo defensivo de páginas y aún quedaban más
        $result->listadoCompleto = false;
        $result->aviso(
            'Paginación de inscripciones',
            'pendiente',
            'Sincronización incompleta: se alcanzó el límite máximo de páginas por ejecución.'
        );
    }

    /**
     * @param  array<string, mixed>  $rawItem
     */
    private function sincronizarItem(array $rawItem, InscripcionSyncResult $result): void
    {
        $rawId = $rawItem['id'] ?? 'desconocido';

        try {
            $datos = $this->parser->enrollment($rawItem);
        } catch (SystemeException $e) {
            $result->fallidos++;
            $result->aviso("Inscripción #{$rawId}", 'error', $e->getMessage());
            Log::warning('Sincronización de inscripciones: ítem inválido', [
                'inscripcion_systeme' => $rawId,
                'mensaje'             => $e->getMessage(),
            ]);

            return;
        }

        $ahora = Carbon::now();

        try {
            [$creado, $estadoVinculacion, $viaPersona] = DB::transaction(function () use ($datos, $ahora) {
                $registro = InscripcionSysteme::query()
                    ->where('id_systeme_inscripcion', $datos['id_systeme_inscripcion'])
                    ->lockForUpdate()
                    ->first();

                $creado = $registro === null;
                $registro ??= new InscripcionSysteme([
                    'id_systeme_inscripcion'   => $datos['id_systeme_inscripcion'],
                    'primera_sincronizacion_en' => $ahora,
                ]);

                // 1. Vincular curso mediante programa.id_systeme_curso
                $programa = Programa::query()
                    ->where('id_systeme_curso', $datos['id_systeme_curso'])
                    ->first();
                $programaId = $programa?->id;

                // 2. Vincular persona: priorizar persona.id_systeme_contacto;
                //    si no existe, reutilizar la regla del webhook por correo normalizado único
                //    sin un ID externo incompatible.
                [$personaId, $viaPersona, $motivoCodigoPersona, $motivoPersona] = $this->resolverPersona(
                    $datos['id_systeme_contacto'],
                    $datos['email_contacto'],
                    $registro->id
                );

                // Conservar el diagnóstico de datos incompletos si la inscripción ya fue evaluada
                // para el mismo contacto y sigue sin existir la ficha en Persona.
                if (
                    $personaId === null
                    && $motivoCodigoPersona === InscripcionSysteme::MOTIVO_PERSONA_NO_ENCONTRADA
                    && $registro->vinculacion_motivo_codigo === InscripcionSysteme::MOTIVO_DATOS_INCOMPLETOS
                    && (string) $registro->id_systeme_contacto === $datos['id_systeme_contacto']
                    && ! empty($registro->vinculacion_motivo)
                ) {
                    $motivoCodigoPersona = InscripcionSysteme::MOTIVO_DATOS_INCOMPLETOS;
                    $motivoPersona = $registro->vinculacion_motivo;
                }

                // 3. Determinar estado global de vinculación y motivo visible
                [$estadoVinculacion, $motivoCodigo, $motivoTexto] = $this->determinarEstadoVinculacion(
                    $programaId,
                    $datos['id_systeme_curso'],
                    $personaId,
                    $motivoCodigoPersona,
                    $motivoPersona
                );

                // Actualizar únicamente los campos de la bandeja local;
                // fecha_matricula se mantiene separada de sincronizado_en y de contacto_registrado_en.
                $registro->forceFill([
                    'id_systeme_curso'          => $datos['id_systeme_curso'],
                    'curso_nombre_systeme'      => $datos['curso_nombre_systeme'],
                    'id_systeme_contacto'       => $datos['id_systeme_contacto'],
                    'email_contacto'            => $datos['email_contacto'],
                    'contacto_registrado_en'    => $datos['contacto_registrado_en'],
                    'access_type'               => $datos['access_type'],
                    'systeme_activo'            => $datos['systeme_activo'],
                    'programa_id'               => $programaId,
                    'persona_id'                => $personaId,
                    'vinculacion_estado'        => $estadoVinculacion,
                    'persona_vinculacion_via'   => $viaPersona,
                    'vinculacion_motivo_codigo' => $motivoCodigo,
                    'vinculacion_motivo'        => $motivoTexto,
                    'sincronizado_en'           => $ahora,
                ]);

                $registro->save();

                return [$creado, $estadoVinculacion, $viaPersona];
            });
        } catch (Throwable $e) {
            $result->fallidos++;
            $result->aviso(
                "Inscripción #{$datos['id_systeme_inscripcion']}",
                'error',
                'No se pudo guardar la inscripción en la bandeja local.'
            );
            Log::warning('Sincronización de inscripciones: error al guardar ítem', [
                'inscripcion_systeme' => $datos['id_systeme_inscripcion'],
                'causa'               => class_basename($e),
            ]);

            return;
        }

        $creado ? $result->creados++ : $result->actualizados++;

        if ($estadoVinculacion === InscripcionSysteme::VINCULACION_VINCULADA) {
            $result->vinculadas++;
            if ($viaPersona === InscripcionSysteme::VIA_ID_CONTACTO) {
                $result->vinculadasPorIdContacto++;
            } elseif ($viaPersona === InscripcionSysteme::VIA_EMAIL) {
                $result->vinculadasPorEmail++;
            }
        } elseif ($estadoVinculacion === InscripcionSysteme::VINCULACION_CONFLICTO) {
            $result->conflictos++;
        } else {
            $result->pendientes++;
        }
    }

    /**
     * Resuelve la vinculación con `Persona` priorizando `id_systeme_contacto` y,
     * en su defecto, aplicando la misma regla del webhook para correo único normalizado.
     *
     * @return array{0: ?int, 1: ?string, 2: ?string, 3: ?string}
     *         [persona_id, persona_vinculacion_via, motivo_codigo, motivo_texto]
     */
    private function resolverPersona(string $contactoId, string $emailNormalizado, ?int $inscripcionSystemeIdActual = null): array
    {
        // Prioridad 1: coincidencia por persona.id_systeme_contacto
        $porIdContacto = Persona::query()
            ->where('id_systeme_contacto', $contactoId)
            ->lockForUpdate()
            ->first();

        if ($porIdContacto !== null) {
            return [
                (int) $porIdContacto->id,
                InscripcionSysteme::VIA_ID_CONTACTO,
                null,
                null,
            ];
        }

        // Prioridad 2: coincidencia por correo normalizado único sin ID externo incompatible
        $coincidenciasCorreo = Persona::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$emailNormalizado])
            ->lockForUpdate()
            ->limit(2)
            ->get();

        if ($coincidenciasCorreo->count() > 1) {
            return [
                null,
                null,
                InscripcionSysteme::MOTIVO_CORREO_DUPLICADO,
                "Conflicto de vinculación: hay varias personas con el correo {$emailNormalizado}; revisar vinculación.",
            ];
        }

        /** @var Persona|null $candidata */
        $candidata = $coincidenciasCorreo->first();

        if ($candidata === null) {
            return [
                null,
                null,
                InscripcionSysteme::MOTIVO_PERSONA_NO_ENCONTRADA,
                "Persona pendiente en Laravel: sin coincidencia por ID de contacto (#{$contactoId}) ni por correo.",
            ];
        }

        $idExternoExistente = $candidata->id_systeme_contacto !== null
            ? trim((string) $candidata->id_systeme_contacto)
            : '';

        if ($idExternoExistente !== '' && $idExternoExistente !== $contactoId) {
            return [
                null,
                null,
                InscripcionSysteme::MOTIVO_CORREO_OTRO_CONTACTO,
                "Conflicto de vinculación: el correo {$emailNormalizado} ya está vinculado a otro contacto de Systeme.io (#{$idExternoExistente}).",
            ];
        }

        // Comprobar además si en la propia bandeja esa Persona ya fue vinculada a otro contacto distinto de Systeme.io
        $otroContactoEnBandeja = InscripcionSysteme::query()
            ->where('persona_id', $candidata->id)
            ->where('id_systeme_contacto', '!=', $contactoId)
            ->when($inscripcionSystemeIdActual !== null, fn ($q) => $q->where('id', '!=', $inscripcionSystemeIdActual))
            ->value('id_systeme_contacto');

        if ($otroContactoEnBandeja !== null) {
            return [
                null,
                null,
                InscripcionSysteme::MOTIVO_CORREO_OTRO_CONTACTO,
                "Conflicto de vinculación: el correo {$emailNormalizado} ya tiene inscripciones asociadas a otro contacto de Systeme.io (#{$otroContactoEnBandeja}).",
            ];
        }

        return [
            (int) $candidata->id,
            InscripcionSysteme::VIA_EMAIL,
            null,
            null,
        ];
    }

    /**
     * @return array{0: string, 1: ?string, 2: ?string}
     *         [vinculacion_estado, vinculacion_motivo_codigo, vinculacion_motivo]
     */
    private function determinarEstadoVinculacion(
        ?int $programaId,
        string $idSystemeCurso,
        ?int $personaId,
        ?string $motivoCodigoPersona,
        ?string $motivoPersona,
    ): array {
        if ($programaId !== null && $personaId !== null) {
            return [
                InscripcionSysteme::VINCULACION_VINCULADA,
                null,
                null,
            ];
        }

        $esConflictoPersona = in_array($motivoCodigoPersona, [
            InscripcionSysteme::MOTIVO_CORREO_DUPLICADO,
            InscripcionSysteme::MOTIVO_CORREO_OTRO_CONTACTO,
        ], true);

        if ($programaId === null && $personaId !== null) {
            return [
                InscripcionSysteme::VINCULACION_PENDIENTE,
                InscripcionSysteme::MOTIVO_CURSO_NO_SINCRONIZADO,
                "Curso #{$idSystemeCurso} pendiente de sincronizar en el módulo Programas.",
            ];
        }

        $mensajes = [];
        if ($motivoPersona !== null) {
            $mensajes[] = $motivoPersona;
        }
        if ($programaId === null) {
            $mensajes[] = "Curso #{$idSystemeCurso} pendiente de sincronizar en Programas.";
        }

        return [
            $esConflictoPersona
                ? InscripcionSysteme::VINCULACION_CONFLICTO
                : InscripcionSysteme::VINCULACION_PENDIENTE,
            $motivoCodigoPersona ?? InscripcionSysteme::MOTIVO_CURSO_NO_SINCRONIZADO,
            mb_substr(implode(' ', $mensajes), 0, 500),
        ];
    }
}

