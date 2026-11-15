<?php

namespace App\Services\Systeme;

use Carbon\CarbonImmutable;
use Throwable;

/**
 * Valida y normaliza cada ítem del listado de inscripciones de Systeme.io
 * (`GET /api/school/enrollments`).
 */
class SystemeEnrollmentParser
{
    /**
     * @param  array<string, mixed>  $item
     * @return array{
     *     id: int,
     *     id_systeme_inscripcion: string,
     *     id_systeme_curso: string,
     *     curso_nombre_systeme: ?string,
     *     id_systeme_contacto: string,
     *     email_contacto: string,
     *     contacto_registrado_en: ?CarbonImmutable,
     *     access_type: string,
     *     systeme_activo: bool
     * }
     */
    public function enrollment(array $item): array
    {
        $enrollmentId = $this->positiveInt($item['id'] ?? null, 'ID de inscripción');

        $contact = $item['contact'] ?? null;
        if (! is_array($contact)) {
            throw new SystemeException("La inscripción #{$enrollmentId} no contiene datos de contacto.");
        }

        $contactId = $this->positiveInt($contact['id'] ?? null, "ID de contacto en inscripción #{$enrollmentId}");
        $email = $this->email($contact['email'] ?? null, $enrollmentId);

        $course = $item['course'] ?? null;
        if (! is_array($course)) {
            throw new SystemeException("La inscripción #{$enrollmentId} no contiene datos del curso.");
        }

        $courseId = $this->positiveInt($course['id'] ?? null, "ID de curso en inscripción #{$enrollmentId}");
        $courseName = $this->text($course['name'] ?? null, 255);

        $accessType = $this->text($item['accessType'] ?? null, 60);
        if ($accessType === null) {
            throw new SystemeException("La inscripción #{$enrollmentId} no informa accessType.");
        }

        if (! is_bool($item['active'] ?? null)) {
            throw new SystemeException("La inscripción #{$enrollmentId} no informa un estado active booleano válido.");
        }

        return [
            'id'                     => $enrollmentId,
            'id_systeme_inscripcion' => (string) $enrollmentId,
            'id_systeme_curso'       => (string) $courseId,
            'curso_nombre_systeme'   => $courseName,
            'id_systeme_contacto'    => (string) $contactId,
            'email_contacto'         => $email,
            'contacto_registrado_en' => $this->date($contact['registeredAt'] ?? null),
            'access_type'            => $accessType,
            'systeme_activo'         => $item['active'],
        ];
    }

    /**
     * Extrae y valida los campos de un contacto devuelto por `GET /api/contacts/{id}`.
     * Si faltan datos obligatorios para crear `Persona` (correo, nombre o apellido),
     * devuelve `completo = false` y la lista de campos faltantes en `faltantes`.
     *
     * @param  array<string, mixed>  $raw
     * @return array{
     *     id: string,
     *     email: ?string,
     *     nombres: ?string,
     *     apellidos: ?string,
     *     completo: bool,
     *     faltantes: list<string>
     * }
     */
    public function contact(array $raw, string $expectedContactId): array
    {
        $expected = trim($expectedContactId);
        if (isset($raw['id']) && (string) $raw['id'] !== $expected) {
            throw new SystemeException("El contacto devuelto no coincide con el ID solicitado (#{$expected}).");
        }

        $emailRaw = is_string($raw['email'] ?? null) ? strtolower(trim($raw['email'])) : '';
        $email = ($emailRaw !== '' && mb_strlen($emailRaw) <= 255 && filter_var($emailRaw, FILTER_VALIDATE_EMAIL))
            ? $emailRaw
            : null;

        $firstName = null;
        $surname = null;

        if (isset($raw['fields']) && is_array($raw['fields'])) {
            foreach ($raw['fields'] as $key => $field) {
                if (is_array($field) && isset($field['slug'])) {
                    $slug = strtolower(trim((string) $field['slug']));
                    $val = $this->text($field['value'] ?? null, 255);
                    if ($slug === 'first_name' && $val !== null) {
                        $firstName = $val;
                    } elseif ($slug === 'surname' && $val !== null) {
                        $surname = $val;
                    }
                } elseif (is_string($key)) {
                    $slug = strtolower(trim($key));
                    $val = $this->text($field, 255);
                    if ($slug === 'first_name' && $val !== null) {
                        $firstName = $val;
                    } elseif ($slug === 'surname' && $val !== null) {
                        $surname = $val;
                    }
                }
            }
        }

        $faltantes = [];
        if ($email === null) {
            $faltantes[] = 'correo (email)';
        }
        if ($firstName === null) {
            $faltantes[] = 'nombre (first_name)';
        }
        if ($surname === null) {
            $faltantes[] = 'apellido (surname)';
        }

        return [
            'id'        => $expected,
            'email'     => $email,
            'nombres'   => $firstName,
            'apellidos' => $surname,
            'completo'  => $faltantes === [],
            'faltantes' => $faltantes,
        ];
    }

    private function positiveInt(mixed $value, string $label): int
    {
        if (! is_int($value) || $value < 1) {
            throw new SystemeException("Valor inválido para {$label}.");
        }

        return $value;
    }

    private function email(mixed $value, int $enrollmentId): string
    {
        if (! is_string($value)) {
            throw new SystemeException("La inscripción #{$enrollmentId} no contiene un correo válido.");
        }

        $normalized = strtolower(trim($value));
        if ($normalized === '' || mb_strlen($normalized) > 255 || ! filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            throw new SystemeException("La inscripción #{$enrollmentId} contiene un correo inválido.");
        }

        return $normalized;
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

    private function date(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->utc();
        } catch (Throwable) {
            return null;
        }
    }
}

