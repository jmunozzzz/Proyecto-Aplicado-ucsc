<?php

namespace App\Services\Systeme;

use App\Models\Leccion;
use App\Models\Modulo;
use App\Models\Programa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sincronización unidireccional Systeme.io → Laravel de cursos, módulos y lecciones.
 *
 * Reglas:
 *  - Registros identificados por id_systeme_curso / id_systeme_modulo / id_systeme_leccion.
 *  - Nunca se borran registros: lo que deja de aparecer se marca con systeme_presente = false.
 *  - La estructura solo se reemplaza si llega completa (hasMore = false) y válida.
 *    Con hasMore = true se conserva la última estructura local válida.
 *  - Los cambios de cada curso se aplican en una transacción.
 *  - Los campos locales (linea, modalidad, categoria, horas, objetivos, requisitos,
 *    duracion_min, competencia_id, etc.) nunca se sobrescriben.
 *  - Solo realiza GET a Systeme.io.
 */
class ProgramaSyncService
{
    public const MENSAJE_PENDIENTE = 'Estructura pendiente: requiere paginación';

    /** Desplazamiento temporal de `orden` para reordenar sin chocar con los índices únicos. */
    private const ORDEN_TEMPORAL = 1_000_000;

    public function __construct(
        private SystemeClient $client,
        private SystemeCourseParser $parser,
    ) {}

    public function sincronizar(): ProgramaSyncResult
    {
        $result = new ProgramaSyncResult();

        $lock = Cache::lock('systeme:sync-programas', 600);
        if (! $lock->get()) {
            $result->errorGeneral = 'Ya hay una sincronización en curso. Inténtalo de nuevo en unos minutos.';

            return $result;
        }

        try {
            $this->ejecutar($result);
        } finally {
            $lock->release();
        }

        return $result;
    }

    private function ejecutar(ProgramaSyncResult $result): void
    {
        try {
            $listado = $this->client->listCourses();
        } catch (SystemeException $e) {
            $result->errorGeneral = 'No se pudo obtener el listado de cursos: ' . $e->getMessage();
            Log::warning('Sincronización de programas: fallo al listar cursos', ['estado_http' => $e->status]);

            return;
        }

        if ($listado['hasMore']) {
            // No se usa un cursor no confirmado: se procesa la primera página y se informa.
            $result->listadoCompleto = false;
            $result->aviso('Listado de cursos', 'pendiente', 'El listado de cursos tiene más páginas (requiere paginación); se procesaron los primeros ' . count($listado['items']) . '.');
        }

        $idsRecibidos = [];

        foreach ($listado['items'] as $item) {
            $courseId = is_array($item) ? ($item['id'] ?? null) : null;
            $nombre = is_array($item) && is_string($item['name'] ?? null) ? $item['name'] : 'Curso sin nombre';

            if (! is_int($courseId) || $courseId < 1) {
                $result->fallidos++;
                $result->aviso($nombre, 'error', 'El listado incluye un curso sin ID válido.');

                continue;
            }

            $idsRecibidos[] = (string) $courseId;
            $this->sincronizarCurso($courseId, $nombre, $item, $result);
        }

        // Solo con el listado completo se puede afirmar que un curso ya no aparece.
        if ($result->listadoCompleto) {
            Programa::query()
                ->whereNotNull('id_systeme_curso')
                ->whereNotIn('id_systeme_curso', $idsRecibidos)
                ->where(fn ($q) => $q->whereNull('systeme_presente')->orWhere('systeme_presente', true))
                ->update(['systeme_presente' => false]);
        }
    }

    private function sincronizarCurso(int $courseId, string $nombreListado, array $item, ProgramaSyncResult $result): void
    {
        $ahora = Carbon::now();

        // 1. Detalle del curso (obligatorio).
        try {
            $curso = $this->parser->course($this->client->getCourse($courseId), $courseId);
        } catch (SystemeException $e) {
            $this->registrarFallo($courseId, $nombreListado, $e, $result);

            return;
        }

        // 2. Estructura (opcional: si falla se conserva la última válida).
        $estructura = null;
        $estadoEstructura = Programa::ESTRUCTURA_COMPLETA;
        $mensajeEstructura = null;

        try {
            $rawStructure = $this->client->getStructure($courseId);
            $expectedModuleIds = $this->parser->moduleIdsFromListItem($item);

            // Si modules-with-lectures no incluye todos los módulos del listado
            // (porque Systeme.io omite los módulos sin lecciones), consultamos course-modules.
            $flatModules = null;
            if ($expectedModuleIds !== null && ($rawStructure['hasMore'] ?? null) === false) {
                $receivedIds = [];
                foreach ($rawStructure['items'] ?? [] as $it) {
                    foreach ($it['modules'] ?? [] as $m) {
                        if (is_int($m['id'] ?? null)) {
                            $receivedIds[] = $m['id'];
                        }
                    }
                }
                if (array_diff($expectedModuleIds, $receivedIds) !== []) {
                    $flatModules = $this->client->getCourseModules($courseId);
                }
            }

            $estructura = $this->parser->structure(
                $rawStructure,
                $courseId,
                $expectedModuleIds,
                $flatModules
            );

            if ($estructura['hasMore']) {
                $estadoEstructura = Programa::ESTRUCTURA_PENDIENTE;
                $mensajeEstructura = self::MENSAJE_PENDIENTE;
                $estructura = null;
            }
        } catch (SystemeException $e) {
            $estadoEstructura = Programa::ESTRUCTURA_ERROR;
            $mensajeEstructura = 'No se actualizó la estructura: ' . $e->getMessage();
            $estructura = null;
        }

        // 3. Guardar todo el curso en una transacción.
        try {
            $creado = DB::transaction(function () use ($curso, $estructura, $estadoEstructura, $mensajeEstructura, $ahora) {
                $programa = Programa::query()
                    ->where('id_systeme_curso', (string) $curso['id'])
                    ->lockForUpdate()
                    ->first();

                $creado = $programa === null;
                $programa ??= new Programa(['id_systeme_curso' => (string) $curso['id']]);

                // Solo campos provenientes de Systeme.io; los locales no se tocan.
                $programa->forceFill([
                    'nombre'                  => $curso['nombre'],
                    'descripcion'             => $curso['descripcion'],
                    'instructor'              => $curso['instructor'],
                    'imagen_url'              => $curso['imagen_url'],
                    'url_publica'             => $curso['url_publica'],
                    'systeme_activo'          => $curso['systeme_activo'],
                    'systeme_creado_en'       => $curso['systeme_creado_en'],
                    'systeme_presente'        => true,
                    'sincronizado_en'         => $ahora,
                    'sincronizacion_mensaje'  => $mensajeEstructura,
                    'estructura_estado'       => $estadoEstructura,
                    'estructura_intentada_en' => $ahora,
                ]);

                if ($estructura !== null) {
                    $programa->estructura_confirmada_en = $ahora;
                }

                $programa->save();

                if ($estructura !== null) {
                    $this->aplicarEstructura($programa, $estructura['modules']);
                }

                return $creado;
            });
        } catch (Throwable $e) {
            $this->registrarFallo($courseId, $curso['nombre'], new SystemeException('No se pudieron guardar los cambios del curso.'), $result, $e);

            return;
        }

        $creado ? $result->creados++ : $result->actualizados++;

        match ($estadoEstructura) {
            Programa::ESTRUCTURA_COMPLETA  => $result->estructurasCompletas++,
            Programa::ESTRUCTURA_PENDIENTE => $result->estructurasPendientes++,
            Programa::ESTRUCTURA_ERROR     => $result->estructurasConError++,
        };

        if ($mensajeEstructura !== null) {
            $tipo = $estadoEstructura === Programa::ESTRUCTURA_PENDIENTE ? 'pendiente' : 'error';
            $result->aviso($curso['nombre'], $tipo, $mensajeEstructura . ' Se conserva la última estructura válida.');
            Log::warning('Sincronización de programas: estructura no actualizada', [
                'curso_systeme' => $courseId,
                'estado'        => $estadoEstructura,
            ]);
        }
    }

    /**
     * Aplica una estructura completa y validada. Se ejecuta dentro de la transacción del curso.
     *
     * @param  array<int, array{id: int, titulo: string, orden: int, lecciones: array<int, array{id: int, titulo: string, orden: int, activo: bool}>}>  $modules
     */
    private function aplicarEstructura(Programa $programa, array $modules): void
    {
        $idsModulos = array_map(fn ($m) => (string) $m['id'], $modules);
        $idsLecciones = [];
        foreach ($modules as $m) {
            foreach ($m['lecciones'] as $l) {
                $idsLecciones[] = (string) $l['id'];
            }
        }

        // Módulos afectados: los del programa y los que llegan (por si cambiaron de curso).
        $modulosAfectados = Modulo::query()
            ->where('programa_id', $programa->id)
            ->orWhereIn('id_systeme_modulo', $idsModulos)
            ->lockForUpdate()
            ->get();

        $leccionesAfectadas = Leccion::query()
            ->whereIn('modulo_id', $modulosAfectados->pluck('id'))
            ->orWhereIn('id_systeme_leccion', $idsLecciones)
            ->lockForUpdate()
            ->get();

        // Orden previo, para recolocar al final lo que ya no aparece.
        $ordenPrevioModulo = $modulosAfectados->pluck('orden', 'id')->all();
        $ordenPrevioLeccion = $leccionesAfectadas->pluck('orden', 'id')->all();

        // Desplazar temporalmente `orden` para evitar choques con UNIQUE(programa_id, orden)
        // y UNIQUE(modulo_id, orden) mientras se reordena.
        if ($modulosAfectados->isNotEmpty()) {
            Modulo::query()->whereIn('id', $modulosAfectados->pluck('id'))
                ->update(['orden' => DB::raw('orden + ' . self::ORDEN_TEMPORAL)]);
        }
        if ($leccionesAfectadas->isNotEmpty()) {
            Leccion::query()->whereIn('id', $leccionesAfectadas->pluck('id'))
                ->update(['orden' => DB::raw('orden + ' . self::ORDEN_TEMPORAL)]);
        }

        $modulosPorSysteme = $modulosAfectados->whereNotNull('id_systeme_modulo')->keyBy('id_systeme_modulo');
        $leccionesPorSysteme = $leccionesAfectadas->whereNotNull('id_systeme_leccion')->keyBy('id_systeme_leccion');

        $modulosVistos = [];
        $leccionesVistas = [];
        $maxOrdenLeccion = [];   // modulo_id local => mayor orden recibido
        $maxOrdenModulo = 0;

        foreach ($modules as $m) {
            $modulo = $modulosPorSysteme->get((string) $m['id']) ?? new Modulo(['id_systeme_modulo' => (string) $m['id']]);
            $modulo->forceFill([
                'programa_id'      => $programa->id,
                'titulo'           => $m['titulo'],
                'orden'            => $m['orden'],
                'systeme_presente' => true,
            ])->save();

            $modulosVistos[$modulo->id] = true;
            $maxOrdenModulo = max($maxOrdenModulo, $m['orden']);
            $maxOrdenLeccion[$modulo->id] = 0;

            foreach ($m['lecciones'] as $l) {
                $leccion = $leccionesPorSysteme->get((string) $l['id']) ?? new Leccion(['id_systeme_leccion' => (string) $l['id']]);
                // duracion_min y competencia_id son locales: no se modifican.
                $leccion->forceFill([
                    'modulo_id'        => $modulo->id,
                    'titulo'           => $l['titulo'],
                    'orden'            => $l['orden'],
                    'systeme_activo'   => $l['activo'],
                    'systeme_presente' => true,
                ])->save();

                $leccionesVistas[$leccion->id] = true;
                $maxOrdenLeccion[$modulo->id] = max($maxOrdenLeccion[$modulo->id], $l['orden']);
            }
        }

        // Módulos del programa que no llegaron: se conservan al final, sin borrarse.
        $restantes = $modulosAfectados
            ->filter(fn (Modulo $mod) => ! isset($modulosVistos[$mod->id]))
            ->sortBy(fn (Modulo $mod) => $ordenPrevioModulo[$mod->id] ?? PHP_INT_MAX)
            ->values();

        foreach ($restantes as $i => $mod) {
            $mod->forceFill([
                'orden'            => $maxOrdenModulo + $i + 1,
                'systeme_presente' => $mod->id_systeme_modulo !== null ? false : $mod->systeme_presente,
            ])->save();
            $maxOrdenLeccion[$mod->id] ??= 0;
        }

        // Lecciones que no llegaron: se conservan al final de su módulo, sin borrarse.
        $leccionesRestantes = $leccionesAfectadas
            ->filter(fn (Leccion $lec) => ! isset($leccionesVistas[$lec->id]))
            ->sortBy(fn (Leccion $lec) => $ordenPrevioLeccion[$lec->id] ?? PHP_INT_MAX)
            ->groupBy('modulo_id');

        foreach ($leccionesRestantes as $moduloId => $grupo) {
            $base = $maxOrdenLeccion[$moduloId] ?? 0;
            foreach ($grupo->values() as $i => $lec) {
                $lec->forceFill([
                    'orden'            => $base + $i + 1,
                    'systeme_presente' => $lec->id_systeme_leccion !== null ? false : $lec->systeme_presente,
                ])->save();
            }
        }
    }

    private function registrarFallo(int $courseId, string $nombre, SystemeException $e, ProgramaSyncResult $result, ?Throwable $causa = null): void
    {
        $result->fallidos++;
        $result->aviso($nombre, 'error', $e->getMessage());

        Log::warning('Sincronización de programas: curso no sincronizado', [
            'curso_systeme' => $courseId,
            'estado_http'   => $e->status,
            'causa'         => $causa ? class_basename($causa) : null,
        ]);

        // Registrar el último error sin modificar ningún otro dato del programa.
        try {
            Programa::query()->where('id_systeme_curso', (string) $courseId)->update([
                'sincronizacion_mensaje' => mb_substr('Último intento fallido: ' . $e->getMessage(), 0, 500),
            ]);
        } catch (Throwable) {
            // Sin acción: el resumen ya informa el fallo.
        }
    }
}

