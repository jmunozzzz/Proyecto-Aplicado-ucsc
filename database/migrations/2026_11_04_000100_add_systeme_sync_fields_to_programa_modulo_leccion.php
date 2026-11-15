<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Prepara programa / modulo / leccion para la sincronización Systeme.io → Laravel.
 *
 * Las tablas existen fuera de las migraciones (esquema creado directamente en
 * MariaDB), por eso se modifican con SQL explícito y solo en MySQL/MariaDB.
 * No se elimina ninguna tabla ni columna existente.
 *
 * Cambios en `programa`:
 *  - codigo, version, linea, modalidad y horas pasan a admitir NULL
 *    (información local pendiente de completar).
 *  - modalidad pasa de (online, presencial, mixto) a (sincronica, asincronica, mixta).
 *    La migración se detiene si hay filas con valores que no encajen.
 *  - Nuevas columnas sincronizadas desde Systeme.io y campos locales
 *    (categoria, objetivos, requisitos).
 */
return new class extends Migration
{
    private const MODALIDADES_NUEVAS = ['sincronica', 'asincronica', 'mixta'];

    public function up(): void
    {
        if (! $this->esMysql() || ! Schema::hasTable('programa')) {
            return;
        }

        $incompatibles = DB::table('programa')
            ->whereNotNull('modalidad')
            ->whereNotIn('modalidad', self::MODALIDADES_NUEVAS)
            ->count();

        if ($incompatibles > 0) {
            throw new RuntimeException(
                "Hay {$incompatibles} programa(s) con una modalidad antigua (online/presencial/mixto). "
                . 'Revísalos manualmente antes de aplicar esta migración; no se modificó nada.'
            );
        }

        DB::statement(<<<'SQL'
            ALTER TABLE `programa`
                MODIFY `codigo`    varchar(255) NULL,
                MODIFY `version`   varchar(255) NULL,
                MODIFY `linea`     varchar(255) NULL CHECK (`linea` in ('abierto','corporativo','in_company')),
                MODIFY `modalidad` varchar(255) NULL CHECK (`modalidad` in ('sincronica','asincronica','mixta')),
                MODIFY `horas`     decimal(8,2) NULL CHECK (`horas` > 0),
                ADD COLUMN `descripcion`              text          NULL AFTER `nombre`,
                ADD COLUMN `instructor`               varchar(255)  NULL AFTER `descripcion`,
                ADD COLUMN `imagen_url`               varchar(2048) NULL AFTER `instructor`,
                ADD COLUMN `url_publica`              varchar(2048) NULL AFTER `imagen_url`,
                ADD COLUMN `categoria`                varchar(255)  NULL AFTER `modalidad`,
                ADD COLUMN `objetivos`                text          NULL AFTER `duracion_descripcion`,
                ADD COLUMN `requisitos`               text          NULL AFTER `objetivos`,
                ADD COLUMN `systeme_activo`           tinyint(1)    NULL AFTER `id_systeme_curso`,
                ADD COLUMN `systeme_presente`         tinyint(1)    NULL AFTER `systeme_activo`,
                ADD COLUMN `systeme_creado_en`        datetime      NULL AFTER `systeme_presente`,
                ADD COLUMN `sincronizado_en`          datetime      NULL AFTER `systeme_creado_en`,
                ADD COLUMN `sincronizacion_mensaje`   varchar(500)  NULL AFTER `sincronizado_en`,
                ADD COLUMN `estructura_estado`        varchar(30)   NULL
                    CHECK (`estructura_estado` in ('completa','pendiente_paginacion','error'))
                    AFTER `sincronizacion_mensaje`,
                ADD COLUMN `estructura_intentada_en`  datetime      NULL AFTER `estructura_estado`,
                ADD COLUMN `estructura_confirmada_en` datetime      NULL AFTER `estructura_intentada_en`
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE `modulo`
                ADD COLUMN `systeme_presente` tinyint(1) NULL AFTER `id_systeme_modulo`
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE `leccion`
                ADD COLUMN `systeme_activo`   tinyint(1) NULL AFTER `id_systeme_leccion`,
                ADD COLUMN `systeme_presente` tinyint(1) NULL AFTER `systeme_activo`
        SQL);
    }

    public function down(): void
    {
        if (! $this->esMysql() || ! Schema::hasTable('programa')) {
            return;
        }

        // Volver a NOT NULL solo es posible si no quedaron datos pendientes.
        $pendientes = DB::table('programa')
            ->where(fn ($q) => $q->whereNull('codigo')->orWhereNull('version')->orWhereNull('linea')
                ->orWhereNull('modalidad')->orWhereNull('horas'))
            ->count();
        $modalidadNueva = DB::table('programa')->whereIn('modalidad', self::MODALIDADES_NUEVAS)->count();

        if ($pendientes > 0 || $modalidadNueva > 0) {
            throw new RuntimeException(
                'No se puede revertir sin perder información: hay programas con campos pendientes '
                . 'o con la nueva modalidad. No se modificó nada.'
            );
        }

        DB::statement('ALTER TABLE `leccion` DROP COLUMN `systeme_activo`, DROP COLUMN `systeme_presente`');
        DB::statement('ALTER TABLE `modulo` DROP COLUMN `systeme_presente`');
        DB::statement(<<<'SQL'
            ALTER TABLE `programa`
                DROP COLUMN `descripcion`, DROP COLUMN `instructor`, DROP COLUMN `imagen_url`,
                DROP COLUMN `url_publica`, DROP COLUMN `categoria`, DROP COLUMN `objetivos`,
                DROP COLUMN `requisitos`, DROP COLUMN `systeme_activo`, DROP COLUMN `systeme_presente`,
                DROP COLUMN `systeme_creado_en`, DROP COLUMN `sincronizado_en`,
                DROP COLUMN `sincronizacion_mensaje`, DROP COLUMN `estructura_estado`,
                DROP COLUMN `estructura_intentada_en`, DROP COLUMN `estructura_confirmada_en`,
                MODIFY `codigo`    varchar(255) NOT NULL,
                MODIFY `version`   varchar(255) NOT NULL,
                MODIFY `linea`     varchar(255) NOT NULL CHECK (`linea` in ('abierto','corporativo','in_company')),
                MODIFY `modalidad` varchar(255) NOT NULL CHECK (`modalidad` in ('online','presencial','mixto')),
                MODIFY `horas`     decimal(8,2) NOT NULL CHECK (`horas` > 0)
        SQL);
    }

    private function esMysql(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};

