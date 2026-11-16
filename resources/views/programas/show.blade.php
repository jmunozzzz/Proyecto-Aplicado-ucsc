@extends('layouts.admin')

@section('title', $programa->nombre . ' | Programas / Cursos')
@section('page_title', 'Programas / Cursos')

@section('breadcrumbs')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Inicio', 'url' => route('dashboard')],
        ['label' => 'Programas / Cursos', 'url' => route('programas.index')],
        ['label' => $programa->nombre],
    ]" />
@endsection

@section('styles')
<style>
    .section-title {
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: #22d3ee;
        margin-bottom: .85rem;
    }
    .info-row {
        display: flex;
        align-items: baseline;
        gap: .75rem;
        padding: .55rem 0;
        border-bottom: 1px solid rgba(51,65,85,.55);
    }
    .info-row:last-child { border-bottom: none; }
    .info-label {
        font-size: .75rem;
        color: #94a3b8;
        min-width: 9.5rem;
        flex-shrink: 0;
    }
    .info-value {
        font-size: .875rem;
        color: #f1f5f9;
        word-break: break-word;
    }

    .course-cover {
        width: 84px;
        height: 84px;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, #0e7490, #06b6d4);
        color: white;
        flex-shrink: 0;
        overflow: hidden;
        border: 1px solid rgba(6,182,212,.35);
        box-shadow: 0 0 24px rgba(6,182,212,.2);
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .25rem .75rem;
        border-radius: 9999px;
        font-size: .7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        white-space: nowrap;
    }
    .badge-activo    { background: rgba(16,185,129,.15); color: #34d399; border: 1px solid rgba(52,211,153,.3); }
    .badge-pendiente { background: rgba(245,158,11,.13); color: #fbbf24; border: 1px solid rgba(251,191,36,.28); }
    .badge-inactivo  { background: rgba(100,116,139,.15); color: #cbd5e1; border: 1px solid rgba(148,163,184,.28); }
    .badge-error     { background: rgba(239,68,68,.14);  color: #fca5a5; border: 1px solid rgba(248,113,113,.3); }
    .badge-default   { background: rgba(6,182,212,.12);  color: #67e8f9; border: 1px solid rgba(103,232,249,.28); }

    .nm-input-dark {
        width: 100%;
        background: #0b1324;
        border: 1px solid rgba(71,85,105,.7);
        border-radius: .6rem;
        color: #e2e8f0;
        padding: .6rem .9rem;
        font-size: .875rem;
        font-family: 'Inter', sans-serif;
        transition: border-color .2s, box-shadow .2s;
    }
    .nm-input-dark::placeholder { color: #64748b; }
    .nm-input-dark:hover { border-color: rgba(100,116,139,.9); }
    .nm-input-dark:focus { outline: none; border-color: #06b6d4; box-shadow: 0 0 0 3px rgba(6,182,212,.18); }
    select.nm-input-dark {
        appearance: none; padding-right: 2.25rem; cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='m19.5 8.25-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right .7rem center; background-size: 1rem;
    }

    details.module-accordion > summary { list-style: none; cursor: pointer; }
    details.module-accordion > summary::-webkit-details-marker { display: none; }
    details.module-accordion[open] .module-chevron { transform: rotate(180deg); }
</style>
@endsection

@section('content')
@php
    $initials = collect(preg_split('/\s+/', trim($programa->nombre)))
        ->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'PR';
    $estructuraConfirmada = $programa->tieneEstructuraConfirmada();
    $esPendientePag = $programa->estructura_estado === \App\Models\Programa::ESTRUCTURA_PENDIENTE;
    $esErrorEst     = $programa->estructura_estado === \App\Models\Programa::ESTRUCTURA_ERROR;
@endphp
<div class="space-y-6">

    {{-- ── Encabezado del curso ── --}}
    <div class="panel p-5 sm:p-6">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5">
            <div class="flex items-start sm:items-center gap-4 min-w-0">
                <div class="course-cover" aria-hidden="true">
                    @if ($programa->imagen_url)
                        <img src="{{ $programa->imagen_url }}" alt="Imagen de {{ $programa->nombre }}" class="w-full h-full object-cover">
                    @else
                        {{ $initials }}
                    @endif
                </div>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2.5 mb-1.5">
                        <h1 class="text-xl sm:text-2xl font-bold font-jakarta text-white">
                            {{ $programa->nombre }}
                        </h1>
                        @if ($programa->systeme_presente === false)
                            <span class="badge badge-pendiente">Ausente en último listado de Systeme.io</span>
                        @elseif ($programa->systeme_activo === true)
                            <span class="badge badge-activo">Activo en Systeme.io</span>
                        @elseif ($programa->systeme_activo === false)
                            <span class="badge badge-inactivo">Inactivo en Systeme.io</span>
                        @endif

                        @if ($programa->linea)
                            <span class="badge badge-default">{{ $programa->lineaLabel() }}</span>
                        @endif
                        @if ($programa->modalidad)
                            <span class="badge badge-inactivo">{{ $programa->modalidadLabel() }}</span>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-400">
                        @if ($programa->instructor)
                            <span class="flex items-center gap-1.5 text-slate-300">
                                <x-icon name="identification" class="w-4 h-4 text-nm-cyan" />
                                Instructor: <strong class="font-medium text-white">{{ $programa->instructor }}</strong>
                            </span>
                        @endif
                        @if ($programa->id_systeme_curso)
                            <span>ID Systeme.io: <strong class="text-slate-300">#{{ $programa->id_systeme_curso }}</strong></span>
                        @endif
                        @if ($programa->sincronizado_en)
                            <span>Sincronizado: <strong class="text-slate-300">{{ $programa->sincronizado_en->format('d/m/Y H:i') }}</strong></span>
                        @endif
                    </div>

                    @if ($programa->url_publica)
                        <div class="mt-2">
                            <a href="{{ $programa->url_publica }}" target="_blank" rel="noopener noreferrer"
                               class="admin-focus inline-flex items-center gap-1.5 text-xs font-medium text-nm-cyan hover:text-cyan-300">
                                <x-icon name="globe" class="w-4 h-4" />
                                <span>{{ $programa->url_publica }}</span>
                                <x-icon name="arrow-up-right" class="w-3.5 h-3.5" />
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 self-stretch sm:self-auto">
                <a href="{{ route('programas.index') }}"
                   class="admin-focus inline-flex items-center justify-center gap-2 text-sm text-slate-300 hover:text-white px-3.5 py-2 rounded-lg border border-slate-700/80 hover:border-nm-cyan hover:bg-slate-800/60 transition-colors">
                    <x-icon name="arrow-left" class="w-4 h-4 text-slate-400" />
                    Volver al listado
                </a>
            </div>
        </div>
    </div>

    {{-- ── Alerta de estado de estructura (si está pendiente o con error) ── --}}
    @if ($esPendientePag || $esErrorEst)
        <div class="panel p-4 border {{ $esPendientePag ? 'border-amber-500/40 bg-amber-500/10 text-amber-100' : 'border-rose-500/40 bg-rose-500/10 text-rose-100' }} flex items-start gap-3"
             role="alert">
            <x-icon name="exclamation-triangle" class="w-5 h-5 shrink-0 {{ $esPendientePag ? 'text-amber-300' : 'text-rose-300' }} mt-0.5" />
            <div class="text-sm space-y-1">
                <p class="font-semibold">
                    {{ $programa->sincronizacion_mensaje ?? ($esPendientePag ? 'Estructura pendiente: requiere paginación' : 'Error al sincronizar la estructura') }}
                </p>
                @if ($estructuraConfirmada)
                    <p class="text-xs text-slate-300">
                        No se reemplazó la estructura: abajo se muestra la última estructura local válida confirmada el
                        <strong>{{ $programa->estructura_confirmada_en->format('d/m/Y H:i') }}</strong>.
                    </p>
                @else
                    <p class="text-xs text-slate-300">
                        Este curso aún no cuenta con una estructura completa confirmada en Laravel. No se muestra ningún conteo parcial como definitivo.
                    </p>
                @endif
            </div>
        </div>
    @elseif ($programa->sincronizacion_mensaje)
        <div class="panel p-4 border border-amber-500/35 bg-amber-500/10 text-amber-100 flex items-start gap-3" role="alert">
            <x-icon name="exclamation-triangle" class="w-5 h-5 shrink-0 text-amber-300 mt-0.5" />
            <p class="text-sm">{{ $programa->sincronizacion_mensaje }}</p>
        </div>
    @endif

    {{-- ── Columnas: Datos de Systeme.io + Formulario de Ficha Académica Local ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Columna izquierda: Datos sincronizados desde Systeme.io --}}
        <div class="panel p-5 lg:col-span-1 space-y-5">
            <div>
                <h3 class="section-title flex items-center gap-2">
                    <x-icon name="globe" class="w-4 h-4 text-nm-cyan" />
                    Datos en Systeme.io
                </h3>
                <div class="space-y-0.5">
                    <div class="info-row">
                        <span class="info-label">ID de curso</span>
                        <span class="info-value">{{ $programa->id_systeme_curso ? '#' . $programa->id_systeme_curso : 'No vinculado' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Estado remoto</span>
                        <span class="info-value">
                            @if ($programa->systeme_activo === true)
                                Activo
                            @elseif ($programa->systeme_activo === false)
                                Inactivo
                            @else
                                —
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Instructor</span>
                        <span class="info-value">{{ $programa->instructor ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Creado en Systeme</span>
                        <span class="info-value">{{ $programa->systeme_creado_en ? $programa->systeme_creado_en->format('d/m/Y') : '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Última sincronización</span>
                        <span class="info-value">{{ $programa->sincronizado_en ? $programa->sincronizado_en->format('d/m/Y H:i') : '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Estructura</span>
                        <span class="info-value">
                            @if ($esPendientePag)
                                <span class="text-amber-300 font-medium">Estructura pendiente: requiere paginación</span>
                            @elseif ($estructuraConfirmada)
                                <span class="text-emerald-300 font-medium">
                                    Confirmada ({{ $modulosPresentes->count() }} módulos · {{ $totalLeccionesPresentes }} lecciones)
                                </span>
                            @else
                                <span class="text-slate-400">Sin confirmar</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Descripción en Systeme.io</h4>
                @if ($programa->descripcion)
                    <div class="rounded-xl bg-slate-900/70 border border-slate-800 p-3.5 text-sm text-slate-300 whitespace-pre-line leading-relaxed">
                        {{ $programa->descripcion }}
                    </div>
                @else
                    <p class="text-xs text-slate-500 italic">Este curso no tiene descripción registrada en Systeme.io.</p>
                @endif
            </div>
        </div>

        {{-- Columna derecha: Información propia de Laravel (editable) --}}
        <div class="panel p-5 sm:p-6 lg:col-span-2">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                <div>
                    <h3 class="section-title mb-0.5 flex items-center gap-2">
                        <x-icon name="clipboard-list" class="w-4 h-4 text-nm-cyan" />
                        Información académica local (Laravel)
                    </h3>
                    <p class="text-xs text-slate-400">
                        Estos campos se completan en NeuroManagement y se conservan intactos durante futuras sincronizaciones con Systeme.io.
                    </p>
                </div>
            </div>

            @can('manage-programas')
                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-xs text-rose-200" role="alert">
                        <p class="font-semibold mb-1">Revisa los campos del formulario:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('programas.update', $programa) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="linea" class="block text-xs font-medium text-slate-300 mb-1.5">
                                Tipo de programa
                            </label>
                            <select id="linea" name="linea" class="nm-input-dark">
                                <option value="">— Pendiente por definir —</option>
                                @foreach (\App\Models\Programa::LINEAS as $key => $label)
                                    <option value="{{ $key }}" @selected(old('linea', $programa->linea) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="modalidad" class="block text-xs font-medium text-slate-300 mb-1.5">
                                Modalidad
                            </label>
                            <select id="modalidad" name="modalidad" class="nm-input-dark">
                                <option value="">— Pendiente por definir —</option>
                                @foreach (\App\Models\Programa::MODALIDADES as $key => $label)
                                    <option value="{{ $key }}" @selected(old('modalidad', $programa->modalidad) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="categoria" class="block text-xs font-medium text-slate-300 mb-1.5">
                                Categoría
                            </label>
                            <input id="categoria" type="text" name="categoria"
                                   value="{{ old('categoria', $programa->categoria) }}"
                                   maxlength="255"
                                   placeholder="Ej.: Liderazgo ejecutivo, Neurociencia aplicada…"
                                   class="nm-input-dark">
                        </div>

                        <div>
                            <label for="horas" class="block text-xs font-medium text-slate-300 mb-1.5">
                                Duración en horas
                            </label>
                            <input id="horas" type="number" name="horas" step="0.5" min="0.5" max="999999.99"
                                   value="{{ old('horas', $programa->horas !== null ? rtrim(rtrim(number_format((float) $programa->horas, 2, '.', ''), '0'), '.') : '') }}"
                                   placeholder="Pendiente (ej.: 24)"
                                   class="nm-input-dark">
                        </div>
                    </div>

                    <div>
                        <label for="objetivos" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Objetivos del programa
                        </label>
                        <textarea id="objetivos" name="objetivos" rows="3"
                                  placeholder="Describe los objetivos de aprendizaje del programa (opcional si aún está pendiente)…"
                                  class="nm-input-dark">{{ old('objetivos', $programa->objetivos) }}</textarea>
                    </div>

                    <div>
                        <label for="requisitos" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Requisitos
                        </label>
                        <textarea id="requisitos" name="requisitos" rows="3"
                                  placeholder="Indica el perfil de ingreso o requisitos previos (opcional si aún está pendiente)…"
                                  class="nm-input-dark">{{ old('requisitos', $programa->requisitos) }}</textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2 border-t border-slate-800/80">
                        <p class="text-xs text-slate-500">
                            Nota: Las fechas de inicio/término y los cupos se gestionarán en el módulo de Cohortes.
                        </p>
                        <button type="submit"
                                class="admin-focus inline-flex items-center justify-center gap-2 bg-gradient-to-r from-nm-cyan to-nm-cyan2
                                       text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:brightness-110 transition">
                            <x-icon name="check-circle" class="w-4 h-4" />
                            Guardar información local
                        </button>
                    </div>
                </form>
            @else
                <div class="space-y-4">
                    <div class="space-y-0.5">
                        <div class="info-row">
                            <span class="info-label">Tipo de programa</span>
                            <span class="info-value">{{ $programa->lineaLabel() ?? 'Pendiente por definir' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Modalidad</span>
                            <span class="info-value">{{ $programa->modalidadLabel() ?? 'Pendiente por definir' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Categoría</span>
                            <span class="info-value">{{ $programa->categoria ?? 'Pendiente por definir' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Duración en horas</span>
                            <span class="info-value">
                                {{ $programa->horas !== null ? rtrim(rtrim(number_format((float) $programa->horas, 2, '.', ''), '0'), '.') . ' h' : 'Pendiente por definir' }}
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Objetivos</span>
                            <span class="info-value whitespace-pre-line">{{ $programa->objetivos ?? 'Pendiente por definir' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Requisitos</span>
                            <span class="info-value whitespace-pre-line">{{ $programa->requisitos ?? 'Pendiente por definir' }}</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 pt-2 border-t border-slate-800/80">
                        Solo lectura: únicamente los administradores activos pueden editar la ficha académica local o sincronizar desde Systeme.io.
                    </p>
                </div>
            @endcan
        </div>
    </div>

    {{-- ── Estructura curricular: Módulos desplegables y lecciones ordenadas ── --}}
    <div class="panel overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="section-title mb-0.5 flex items-center gap-2">
                    <x-icon name="academic-cap" class="w-4 h-4 text-nm-cyan" />
                    Módulos y lecciones
                    @if ($estructuraConfirmada)
                        <span class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-nm-cyan/10 text-nm-cyan border border-nm-cyan/25 normal-case tracking-normal">
                            {{ $modulosPresentes->count() }} {{ $modulosPresentes->count() === 1 ? 'módulo' : 'módulos' }}
                            · {{ $totalLeccionesPresentes }} {{ $totalLeccionesPresentes === 1 ? 'lección confirmada' : 'lecciones confirmadas' }}
                        </span>
                    @else
                        <span class="ml-1 badge badge-pendiente normal-case">Estructura incompleta / sin confirmar</span>
                    @endif
                </h3>
                <p class="text-xs text-slate-400">
                    Ordenados por posición ascendente según Systeme.io. El estado «Activa / Inactiva» indica la publicación de la lección en Systeme.io (no representa progreso, pago ni acceso individual del alumno).
                </p>
            </div>

            @if ($estructuraConfirmada && $modulosPresentes->isNotEmpty())
                <div class="flex items-center gap-2 text-xs text-slate-400 shrink-0">
                    <span class="badge badge-activo">{{ $leccionesActivas }} activas</span>
                    <span class="badge badge-inactivo">{{ $totalLeccionesPresentes - $leccionesActivas }} inactivas</span>
                </div>
            @endif
        </div>

        @if (! $estructuraConfirmada || $modulosPresentes->isEmpty())
            <div class="py-14 px-6 text-center text-slate-400 text-sm">
                <x-icon name="academic-cap" class="w-8 h-8 mx-auto text-slate-600 mb-2" />
                @if ($esPendientePag)
                    <p class="font-semibold text-amber-200">Estructura pendiente: requiere paginación</p>
                    <p class="text-xs text-slate-400 mt-1 max-w-lg mx-auto">
                        El curso supera el límite de una página en Systeme.io y aún no había una estructura local previa confirmada.
                    </p>
                @else
                    <p>No hay módulos confirmados para mostrar en este programa.</p>
                @endif
            </div>
        @else
            <div class="divide-y divide-slate-800/80">
                @foreach ($modulosPresentes as $modulo)
                    @php
                        $leccionesModulo = $modulo->lecciones->filter(
                            fn ($l) => $l->systeme_presente === null || $l->systeme_presente === true
                        );
                        $activasModulo = $leccionesModulo->where('systeme_activo', true)->count();
                    @endphp
                    <details class="module-accordion group" @if ($loop->first) open @endif>
                        <summary class="admin-focus flex items-center justify-between gap-4 px-5 py-4 hover:bg-slate-900/60 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-8 h-8 shrink-0 rounded-lg bg-nm-cyan/10 border border-nm-cyan/25 text-nm-cyan text-xs font-bold flex items-center justify-center">
                                    {{ $modulo->orden }}
                                </span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-white text-sm sm:text-base truncate">{{ $modulo->titulo }}</p>
                                    <p class="text-xs text-slate-400">
                                        Posición {{ $modulo->orden }}
                                        @if ($modulo->id_systeme_modulo)
                                            · ID Systeme #{{ $modulo->id_systeme_modulo }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <span class="text-xs text-slate-300 font-medium">
                                    {{ $leccionesModulo->count() }} {{ $leccionesModulo->count() === 1 ? 'lección' : 'lecciones' }}
                                    <span class="hidden sm:inline text-slate-500">({{ $activasModulo }} {{ $activasModulo === 1 ? 'activa' : 'activas' }})</span>
                                </span>
                                <x-icon name="chevron-down" class="module-chevron w-4 h-4 text-slate-400 transition-transform duration-200" />
                            </div>
                        </summary>

                        <div class="bg-[#0b1324]/90 border-t border-slate-800/70 px-5 py-3">
                            @if ($leccionesModulo->isEmpty())
                                <p class="text-xs text-slate-500 py-2">Este módulo no tiene lecciones registradas.</p>
                            @else
                                <ol class="divide-y divide-slate-800/60">
                                    @foreach ($leccionesModulo as $leccion)
                                        <li class="py-2.5 flex items-center justify-between gap-3 text-sm">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <span class="w-6 h-6 shrink-0 rounded bg-slate-800 text-slate-300 text-xs font-semibold flex items-center justify-center">
                                                    {{ $leccion->orden }}
                                                </span>
                                                <span class="text-slate-200 truncate">{{ $leccion->titulo }}</span>
                                                @if ($leccion->id_systeme_leccion)
                                                    <span class="hidden md:inline text-xs text-slate-500">#{{ $leccion->id_systeme_leccion }}</span>
                                                @endif
                                            </div>

                                            <div class="shrink-0">
                                                @if ($leccion->systeme_activo === true)
                                                    <span class="badge badge-activo">Activa</span>
                                                @elseif ($leccion->systeme_activo === false)
                                                    <span class="badge badge-inactivo">Inactiva</span>
                                                @else
                                                    <span class="badge badge-default">—</span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection

