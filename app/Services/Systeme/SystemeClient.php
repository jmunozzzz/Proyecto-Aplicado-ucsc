<?php

namespace App\Services\Systeme;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Cliente de solo lectura para la API pública de Systeme.io (https://api.systeme.io).
 *
 * - Solo realiza peticiones GET.
 * - La clave se toma de config('services.systeme.api_key') y se envía únicamente
 *   en la cabecera X-API-Key; nunca se incluye en mensajes de error ni registros.
 */
class SystemeClient
{
    public const BASE_URL = 'https://api.systeme.io';

    /** Máximo permitido por la API para limit. */
    public const MAX_LIMIT = 100;

    private string $apiKey;

    public function __construct(
        ?string $apiKey = null,
        private int $timeout = 20,
        private int $connectTimeout = 10,
        private int $maxRetryAfter = 5,
    ) {
        $this->apiKey = (string) ($apiKey ?? config('services.systeme.api_key'));
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Primera página del listado de cursos (activos e inactivos).
     *
     * @return array{items: array<int, array<string, mixed>>, hasMore: bool}
     */
    public function listCourses(): array
    {
        $json = $this->get('/api/school/courses', ['limit' => self::MAX_LIMIT]);

        if (! isset($json['items']) || ! is_array($json['items']) || ! array_key_exists('hasMore', $json)) {
            throw new SystemeException('El listado de cursos no tiene el formato esperado (items/hasMore).');
        }

        return ['items' => array_values($json['items']), 'hasMore' => $json['hasMore'] === true];
    }

    /** @return array<string, mixed> */
    public function getCourse(int $courseId): array
    {
        return $this->get('/api/school/courses/' . $courseId);
    }

    /**
     * Estructura de módulos y lecciones (una sola página, limit=100).
     *
     * @return array<string, mixed>
     */
    public function getStructure(int $courseId): array
    {
        return $this->get(
            '/api/school/courses/' . $courseId . '/modules-with-lectures',
            ['limit' => self::MAX_LIMIT]
        );
    }

    /**
     * Lista plana de módulos de un curso (incluye módulos sin lecciones, que
     * modules-with-lectures omite).
     *
     * @return array<string, mixed>
     */
    public function getCourseModules(int $courseId): array
    {
        return $this->get(
            '/api/school/course-modules',
            ['courseId' => $courseId, 'limit' => self::MAX_LIMIT]
        );
    }

    /**
     * Página del listado de inscripciones de la escuela (`GET /api/school/enrollments`)
     * con `limit=100` y `order=desc`.
     *
     * @return array{items: array<int, array<string, mixed>>, hasMore: bool}
     */
    public function listEnrollments(?int $startingAfter = null, int $limit = self::MAX_LIMIT, ?string $courseId = null): array
    {
        $query = [
            'limit' => $limit,
            'order' => 'desc',
        ];

        if ($startingAfter !== null) {
            $query['startingAfter'] = $startingAfter;
        }

        if ($courseId !== null && $courseId !== '') {
            $query['course'] = $courseId;
        }

        $json = $this->get('/api/school/enrollments', $query);

        if (! isset($json['items']) || ! is_array($json['items']) || ! array_key_exists('hasMore', $json) || ! is_bool($json['hasMore'])) {
            throw new SystemeException('El listado de inscripciones no tiene el formato esperado (items/hasMore).');
        }

        return [
            'items'   => array_values($json['items']),
            'hasMore' => $json['hasMore'] === true,
        ];
    }

    /**
     * Detalle completo de un contacto (`GET /api/contacts/{id}`), incluyendo `fields`.
     *
     * @return array<string, mixed>
     */
    public function getContact(int|string $contactId): array
    {
        $id = trim((string) $contactId);
        if ($id === '' || ! ctype_digit($id) || (int) $id < 1) {
            throw new SystemeException('El identificador de contacto de Systeme.io no es válido.');
        }

        return $this->get('/api/contacts/' . $id);
    }

    /** @return array<string, mixed> */
    private function get(string $path, array $query = [], int $attempt = 1): array
    {
        if (! $this->isConfigured()) {
            throw new SystemeException('La clave de la API de Systeme.io no está configurada (services.systeme.api_key).');
        }

        try {
            $response = Http::baseUrl(self::BASE_URL)
                ->withHeaders(['X-API-Key' => $this->apiKey])
                ->acceptJson()
                ->timeout($this->timeout)
                ->connectTimeout($this->connectTimeout)
                ->get($path, $query);
        } catch (ConnectionException) {
            throw new SystemeException('No se pudo conectar con Systeme.io o se agotó el tiempo de espera.');
        } catch (Throwable $e) {
            throw new SystemeException('Error inesperado al consultar Systeme.io: ' . $this->sanitize(class_basename($e)));
        }

        if ($response->status() === 429) {
            $retryAfter = (int) ($response->header('Retry-After') ?: 0);

            // Un único reintento si la espera indicada es breve; si no, se informa.
            if ($attempt === 1 && $retryAfter > 0 && $retryAfter <= $this->maxRetryAfter) {
                sleep($retryAfter);

                return $this->get($path, $query, 2);
            }

            throw new SystemeException(
                'Systeme.io limitó las peticiones (HTTP 429).'
                . ($retryAfter > 0 ? " Reintenta en {$retryAfter} s." : ' Reintenta más tarde.'),
                429
            );
        }

        if (! $response->successful()) {
            throw new SystemeException(
                "Systeme.io respondió HTTP {$response->status()}" . $this->errorDetail($response) . '.',
                $response->status()
            );
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new SystemeException('Systeme.io devolvió una respuesta que no es JSON válido.', $response->status());
        }

        return $json;
    }

    private function errorDetail(Response $response): string
    {
        $json = $response->json();
        $detail = is_array($json) ? ($json['detail'] ?? $json['title'] ?? $json['message'] ?? null) : null;

        return is_string($detail) && $detail !== '' ? ': ' . $this->sanitize($detail) : '';
    }

    /** Elimina la clave, URLs con parámetros y recorta el texto. */
    private function sanitize(string $text): string
    {
        if ($this->apiKey !== '') {
            $text = str_replace($this->apiKey, '[oculto]', $text);
        }
        $text = preg_replace('#https?://\S+#i', '[url]', $text) ?? '';

        return mb_strimwidth(trim($text), 0, 200, '…');
    }
}

