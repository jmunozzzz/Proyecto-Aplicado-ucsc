<?php

namespace App\Services\Systeme;

use Carbon\CarbonImmutable;
use Throwable;

/**
 * Valida y normaliza las respuestas de Systeme.io antes de guardarlas.
 * Solo extrae los campos comprobados durante las pruebas de la API.
 */
class SystemeCourseParser
{
    /**
     * Datos del curso a partir del detalle (GET /api/school/courses/{id}).
     * No se conservan courseOwnerUrl, shareUrl ni courseEditUrl.
     *
     * @return array{id: int, nombre: string, descripcion: ?string, instructor: ?string,
     *               imagen_url: ?string, url_publica: ?string, systeme_activo: bool,
     *               systeme_creado_en: ?CarbonImmutable}
     */
    public function course(array $detail, int $expectedId): array
    {
        $id = $detail['id'] ?? null;
        if (! is_int($id) || $id !== $expectedId) {
            throw new SystemeException('El detalle del curso no corresponde al ID solicitado.');
        }

        $nombre = $this->text($detail['name'] ?? null, 255);
        if ($nombre === null) {
            throw new SystemeException('El curso no tiene nombre.');
        }

        if (! is_bool($detail['active'] ?? null)) {
            throw new SystemeException('El curso no informa un estado activo válido.');
        }

        return [
            'id'                => $id,
            'nombre'            => $nombre,
            'descripcion'       => $this->text($detail['description'] ?? null),
            'instructor'        => $this->text($detail['instructorName'] ?? null, 255),
            'imagen_url'        => $this->url(is_array($detail['image'] ?? null) ? ($detail['image']['url'] ?? null) : null),
            'url_publica'       => $this->url($detail['coursePublicUrl'] ?? null),
            'systeme_activo'    => $detail['active'],
            'systeme_creado_en' => $this->date($detail['createdAt'] ?? null),
        ];
    }

    /**
     * Valida la respuesta de modules-with-lectures.
     *
     * @param  int[]|null  $expectedModuleIds  IDs de módulos informados por el listado de cursos.
     * @param  array<string, mixed>|null  $flatModulesJson  Respuesta de /api/school/course-modules cuando hay módulos vacíos.
     * @return array{hasMore: bool, modules: array<int, array{id: int, titulo: string, orden: int,
     *               lecciones: array<int, array{id: int, titulo: string, orden: int, activo: bool}>}>}
     */
    public function structure(array $json, int $courseId, ?array $expectedModuleIds = null, ?array $flatModulesJson = null): array
    {
        if (! array_key_exists('hasMore', $json) || ! is_bool($json['hasMore'])) {
            throw new SystemeException('La estructura no informa hasMore.');
        }
        if (! isset($json['items']) || ! is_array($json['items'])) {
            throw new SystemeException('La estructura no contiene items.');
        }

        // Con hasMore=true la respuesta es parcial: no se valida ni se usa.
        if ($json['hasMore'] === true) {
            return ['hasMore' => true, 'modules' => []];
        }

        $modules = [];
        $lectureOwner = [];

        foreach ($json['items'] as $item) {
            if (! is_array($item) || ($item['courseId'] ?? null) !== $courseId) {
                throw new SystemeException('La estructura contiene datos de otro curso.');
            }
            if (! isset($item['modules']) || ! is_array($item['modules'])) {
                throw new SystemeException('La estructura no contiene la lista de módulos.');
            }

            foreach ($item['modules'] as $m) {
                $mid = $this->positiveInt($m['id'] ?? null, 'ID de módulo');
                $titulo = $this->text($m['name'] ?? null, 255)
                    ?? throw new SystemeException("El módulo {$mid} no tiene nombre.");
                $orden = $this->positiveInt($m['position'] ?? null, "posición del módulo {$mid}");

                if (isset($modules[$mid]) && ($modules[$mid]['orden'] !== $orden || $modules[$mid]['titulo'] !== $titulo)) {
                    throw new SystemeException("El módulo {$mid} aparece con datos distintos.");
                }
                $modules[$mid] ??= ['id' => $mid, 'titulo' => $titulo, 'orden' => $orden, 'lecciones' => []];

                if (! isset($m['lectures']) || ! is_array($m['lectures'])) {
                    throw new SystemeException("El módulo {$mid} no contiene la lista de lecciones.");
                }

                foreach ($m['lectures'] as $l) {
                    $lid = $this->positiveInt($l['id'] ?? null, 'ID de lección');
                    if (isset($lectureOwner[$lid])) {
                        throw new SystemeException("La lección {$lid} aparece más de una vez.");
                    }
                    $lectureOwner[$lid] = $mid;

                    if (! is_bool($l['active'] ?? null)) {
                        throw new SystemeException("La lección {$lid} no informa un estado activo válido.");
                    }

                    $modules[$mid]['lecciones'][$lid] = [
                        'id'     => $lid,
                        'titulo' => $this->text($l['name'] ?? null, 255)
                            ?? throw new SystemeException("La lección {$lid} no tiene nombre."),
                        'orden'  => $this->positiveInt($l['position'] ?? null, "posición de la lección {$lid}"),
                        'activo' => $l['active'],
                    ];
                }
            }
        }

        // modules-with-lectures omite los módulos sin lecciones; si se proporcionó
        // course-modules, completamos los módulos vacíos con su posición oficial.
        if (is_array($flatModulesJson)) {
            if (($flatModulesJson['hasMore'] ?? null) === true) {
                return ['hasMore' => true, 'modules' => []];
            }
            foreach ($flatModulesJson['items'] ?? [] as $fm) {
                if (! is_array($fm) || ($fm['courseId'] ?? null) !== $courseId) {
                    continue;
                }
                $mid = $this->positiveInt($fm['id'] ?? null, 'ID de módulo');
                $titulo = $this->text($fm['name'] ?? null, 255)
                    ?? throw new SystemeException("El módulo {$mid} no tiene nombre.");
                $orden = $this->positiveInt($fm['position'] ?? null, "posición del módulo {$mid}");

                if (! isset($modules[$mid])) {
                    $modules[$mid] = ['id' => $mid, 'titulo' => $titulo, 'orden' => $orden, 'lecciones' => []];
                }
            }
        }

        $this->assertUnique(array_column($modules, 'orden'), 'Hay módulos con la misma posición.');
        foreach ($modules as $mid => $m) {
            $this->assertUnique(array_column($m['lecciones'], 'orden'), "Hay lecciones con la misma posición en el módulo {$mid}.");
        }

        if ($expectedModuleIds !== null) {
            $faltantes = array_diff($expectedModuleIds, array_keys($modules));
            if ($faltantes !== []) {
                throw new SystemeException(
                    'La estructura no incluye ' . count($faltantes) . ' módulo(s) que sí aparecen en el listado del curso.'
                );
            }
        }

        // Orden por position ascendente.
        uasort($modules, fn ($a, $b) => $a['orden'] <=> $b['orden']);
        foreach ($modules as &$m) {
            uasort($m['lecciones'], fn ($a, $b) => $a['orden'] <=> $b['orden']);
            $m['lecciones'] = array_values($m['lecciones']);
        }
        unset($m);

        return ['hasMore' => false, 'modules' => array_values($modules)];
    }

    /** @return int[]|null IDs de módulos del ítem del listado, o null si no vienen. */
    public function moduleIdsFromListItem(array $item): ?array
    {
        if (! isset($item['modules']) || ! is_array($item['modules'])) {
            return null;
        }

        $ids = [];
        foreach ($item['modules'] as $m) {
            if (is_array($m) && is_int($m['id'] ?? null) && $m['id'] > 0) {
                $ids[] = $m['id'];
            }
        }

        return $ids;
    }

    private function positiveInt(mixed $value, string $label): int
    {
        if (! is_int($value) || $value < 1) {
            throw new SystemeException("Valor inválido para {$label}.");
        }

        return $value;
    }

    private function text(mixed $value, ?int $max = null): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        return $max ? mb_substr($value, 0, $max) : $value;
    }

    private function url(mixed $value): ?string
    {
        if (! is_string($value) || strlen($value) > 2048) {
            return null;
        }
        $value = trim($value);
        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

        return filter_var($value, FILTER_VALIDATE_URL) && in_array($scheme, ['http', 'https'], true) ? $value : null;
    }

    private function date(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || $value === '') {
            return null;
        }
        try {
            return CarbonImmutable::parse($value)->utc();
        } catch (Throwable) {
            return null;
        }
    }

    private function assertUnique(array $values, string $message): void
    {
        if (count($values) !== count(array_unique($values))) {
            throw new SystemeException($message);
        }
    }
}

