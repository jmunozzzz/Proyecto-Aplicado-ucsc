<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crea la tabla `inscripcion_systeme` como bandeja local de inscripciones
 * externas sincronizadas desde Systeme.io (`GET /api/school/enrollments`).
 *
 * Se mantiene separada de la tabla académica `inscripcion` (que exige
 * `cohorte_id`, `consentimiento_id`, `via` y `financiamiento` NOT NULL),
 * permitiendo importar, auditar y vincular inscripciones externas con
 * `Programa` y `Persona` antes de generar matrículas académicas.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inscripcion_systeme')) {
            return;
        }

        $tienePrograma = Schema::hasTable('programa');
        $tienePersona = Schema::hasTable('persona');
        $tieneInscripcion = Schema::hasTable('inscripcion');

        Schema::create('inscripcion_systeme', function (Blueprint $table) use ($tienePrograma, $tienePersona, $tieneInscripcion) {
            // Se usa bigInteger con signo para coincidir exactamente con bigint(20) de programa/persona/inscripcion en MariaDB.
            $table->bigInteger('id', true);

            // Identificadores externos y metadatos de Systeme.io
            $table->string('id_systeme_inscripcion', 255)->unique();
            $table->string('id_systeme_curso', 255)->index();
            $table->string('curso_nombre_systeme', 255)->nullable();
            $table->string('id_systeme_contacto', 255)->index();
            $table->string('email_contacto', 255)->index();
            $table->dateTime('contacto_registrado_en')->nullable();

            // Estado y tipo de acceso en Systeme.io
            $table->string('access_type', 60);
            $table->boolean('systeme_activo');

            // Vinculación con modelos locales
            $table->bigInteger('programa_id')->nullable()->index();
            $table->bigInteger('persona_id')->nullable()->index();
            $table->bigInteger('inscripcion_id')->nullable()->index();

            // Estado y trazabilidad de la vinculación ('vinculada', 'pendiente', 'conflicto')
            $table->string('vinculacion_estado', 30)->index();
            $table->string('persona_vinculacion_via', 30)->nullable();
            $table->string('vinculacion_motivo_codigo', 60)->nullable()->index();
            $table->string('vinculacion_motivo', 500)->nullable();

            // Fechas separadas: matrícula académica (futura) vs sincronización desde Systeme.io
            $table->dateTime('fecha_matricula')->nullable();
            $table->dateTime('primera_sincronizacion_en');
            $table->dateTime('sincronizado_en')->index();

            if ($tienePrograma) {
                $table->foreign('programa_id')
                    ->references('id')
                    ->on('programa')
                    ->nullOnDelete();
            }

            if ($tienePersona) {
                $table->foreign('persona_id')
                    ->references('id')
                    ->on('persona')
                    ->nullOnDelete();
            }

            if ($tieneInscripcion) {
                $table->foreign('inscripcion_id')
                    ->references('id')
                    ->on('inscripcion')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripcion_systeme');
    }
};

