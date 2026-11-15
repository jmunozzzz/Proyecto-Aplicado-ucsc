@extends('layouts.admin')

@section('title', 'Inscripciones')
@section('page_title', 'Inscripciones')

@section('breadcrumbs')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Inicio', 'url' => route('dashboard')],
        ['label' => 'Inscripciones'],
    ]" />
@endsection

@section('styles')
<style>
    .stat-card {
        background: linear-gradient(135deg, #111a2e, #0f172a);
        border: 1px solid rgba(51,65,85,.65);
        border-radius: 1rem;
        padding: 1.15rem 1.25rem;
        transition: border-color .2s, box-shadow .2s;
    }
    .stat-card:hover { border-color: rgba(6,182,212,.4); box-shadow: 0 0 24px rgba(6,182,212,.08); }

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

    .nm-table { width: 100%; min-width: 820px; border-collapse: collapse; }
    .nm-table thead tr { background: rgba(30,41,59,.55); border-bottom: 1px solid rgba(51,65,85,.8); }
    .nm-table th {
        padding: .85rem 1rem; text-align: left;
        font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em;
        color: #94a3b8; white-space: nowrap;
    }
    .nm-table th a { color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: .3rem; border-radius: .25rem; }
    .nm-table th a:hover { color: #22d3ee; }
    .nm-table tbody tr { border-bottom: 1px solid rgba(30,41,59,.9); transition: background .15s; }
    .nm-table tbody tr:last-child { border-bottom: none; }
    .nm-table tbody tr:hover { background: rgba(6,182,212,.045); }
    .nm-table td { padding: .85rem 1rem; font-size: .875rem; vertical-align: middle; }

    .badge {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .2rem .65rem; border-radius: 9999px;
        font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; white-space: nowrap;
    }
    .badge-activo    { background: rgba(16,185,129,.15); color: #34d399; border: 1px solid rgba(52,211,153,.3); }
    .badge-pendiente { background: rgba(245,158,11,.13); color: #fbbf24; border: 1px solid rgba(251,191,36,.28); }
    .badge-inactivo  { background: rgba(100,116,139,.15); color: #cbd5e1; border: 1px solid rgba(148,163,184,.28); }
    .badge-error     { background: rgba(239,68,68,.14);  color: #fca5a5; border: 1px solid rgba(248,113,113,.3); }
    .badge-default   { background: rgba(6,182,212,.12);  color: #67e8f9; border: 1px solid rgba(103,232,249,.28); }

    .pg-btn {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 2.1rem; height: 2.1rem; padding: 0 .5rem;
        border-radius: .5rem; font-size: .8rem; font-weight: 500;
        border: 1px solid rgba(71,85,105,.6); color: #cbd5e1; background: #0b1324;
        text-decoration: none; transition: all .15s;
    }
    .pg-btn:hover  { background: rgba(6,182,212,.12); color: #22d3ee; border-color: #06b6d4; }
    .pg-btn.active { background: linear-gradient(135deg,#06b6d4,#0891b2); color: white; border-color: transparent; }
    .pg-btn.disabled { opacity: .35; pointer-events: none; }

    .sort-asc::after  { content: ' ↑'; }
    .sort-desc::after { content: ' ↓'; }
</style>
@endsection

@section('content')
@php
    $syncResult = session('sync_result');
    $importResult = session('import_result');
    $cursoSeleccionadoImport = trim((string) request('curso', ''));
    if ($cursoSeleccionadoImport === '' || $cursoSeleccionadoImport === 'sin_curso') {
        $cursoSeleccionadoImport = \App\Services\Systeme\InscripcionSyncService::CURSO_DEFAULT_SYSTEME_ID;
    }
@endphp
<div class="space-y-6">

    {{-- ── Encabezado y acciones administrativas ── --}}
    <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">
        <div>
            <h2 class="text-2xl lg:text-3xl font-bold font-jakarta text-white">
                Bandeja de <span class="text-gradient-cyan">Inscripciones</span>
            </h2>
            <p class="text-slate-400 text-sm mt-1">
                Inscripciones sincronizadas desde Systeme.io (solo lectura remota) y su estado de vinculación con Personas y Programas.
            </p>
            <p class="text-xs text-slate-500 mt-1">
                Última sincronización:
                @if ($ultimaSincronizacion)
                    <span class="text-slate-300 font-medium">{{ \Carbon\Carbon::parse($ultimaSincronizacion)->format('d/m/Y H:i') }}</span>
                @else
                    <span class="text-amber-300/90">Aún no se ha ejecutado ninguna sincronización</span>
                @endif
            </p>
        </div>

        @can('manage-inscripciones')
            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 shrink-0">
                <form method="POST" action="{{ route('inscripciones.import-alumnos') }}" id="import-alumnos-form"
                      class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    @csrf
                    <label for="import-curso-select" class="sr-only">Curso para importar alumnos pendientes</label>
                    <select name="curso" id="import-curso-select" class="nm-input-dark sm:!w-64 !py-2 text-xs"
                            title="Curso cuyas inscripciones pendientes se procesarán">
                        @php
                            $tieneEvolucionEnLista = $programas->contains(fn ($p) => (string) $p->id_systeme_curso === '674252');
                        @endphp
                        @if (! $tieneEvolucionEnLista)
                            <option value="674252" @selected($cursoSeleccionadoImport === '674252')>
                                Evolución Consciente (#674252)
                            </option>
                        @endif
                        @foreach ($programas as $prog)
                            @php
                                $valImportCurso = $prog->id_systeme_curso ?? (string) $prog->id;
                                $isSelectedImport = (string) $cursoSeleccionadoImport === (string) $valImportCurso
                                    || (string) $cursoSeleccionadoImport === (string) $prog->id;
                            @endphp
                            <option value="{{ $valImportCurso }}" @selected($isSelectedImport)>
                                {{ $prog->nombre }} @if($prog->id_systeme_curso) (#{{ $prog->id_systeme_curso }}) @endif
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" id="import-alumnos-button"
                            class="admin-focus inline-flex items-center justify-center gap-2
                                   border border-nm-cyan/50 bg-nm-cyan/10 text-cyan-200 hover:bg-nm-cyan/20 hover:border-nm-cyan
                                   text-sm font-semibold px-4 py-2.5 rounded-xl transition whitespace-nowrap">
                        <x-icon name="users" class="w-4 h-4 text-nm-cyan" />
                        <span id="import-alumnos-button-label">Importar alumnos pendientes</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('inscripciones.sync') }}" id="sync-form" class="shrink-0">
                    @csrf
                    <button type="submit" id="sync-button"
                            class="admin-focus w-full sm:w-auto inline-flex items-center justify-center gap-2.5
                                   bg-gradient-to-r from-nm-cyan to-nm-cyan2 text-white text-sm font-semibold
                                   px-4 py-2.5 rounded-xl shadow-lg shadow-cyan-500/20 hover:brightness-110 transition whitespace-nowrap">
                        <x-icon name="arrow-path" class="w-4 h-4" />
                        <span id="sync-button-label">Sincronizar desde Systeme.io</span>
                    </button>
                </form>
            </div>
        @endcan
    </div>

    {{-- ── Aviso si falta ejecutar la migración ── --}}
    @if (! empty($migracionPendiente))
        <section class="panel p-4 border border-amber-500/40 bg-amber-500/5 text-sm text-amber-200 flex items-start gap-3">
            <x-icon name="exclamation-triangle" class="w-5 h-5 text-amber-300 shrink-0 mt-0.5" />
            <div>
                <p class="font-semibold text-white">Migración pendiente</p>
                <p class="text-xs text-slate-300 mt-0.5">
                    La tabla <code class="text-nm-cyan">inscripcion_systeme</code> aún no existe en la base de datos activa.
                    Ejecuta <code class="text-nm-cyan">php artisan migrate</code> para habilitar el almacenamiento de la bandeja.
                </p>
            </div>
        </section>
    @endif

    {{-- ── Resumen de la última ejecución de sincronización ── --}}
    @if (is_array($syncResult))
        @php
            $incompleta = ! empty($syncResult['error_general'])
                || empty($syncResult['listado_completo'])
                || ($syncResult['fallidos'] ?? 0) > 0;
            $conConflictos = ($syncResult['conflictos'] ?? 0) > 0;
        @endphp
        <section class="panel p-5 border {{ $incompleta ? 'border-rose-500/40 bg-rose-500/5' : ($conConflictos ? 'border-amber-500/40 bg-amber-500/5' : 'border-emerald-500/35 bg-emerald-500/5') }}"
                 role="status" aria-label="Resumen de sincronización de inscripciones">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span class="w-10 h-10 shrink-0 rounded-xl flex items-center justify-center
                                 {{ $incompleta ? 'bg-rose-500/15 text-rose-300 border border-rose-400/30' : ($conConflictos ? 'bg-amber-500/15 text-amber-300 border border-amber-400/30' : 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/30') }}">
                        <x-icon :name="$incompleta || $conConflictos ? 'exclamation-triangle' : 'check-circle'" class="w-5 h-5" />
                    </span>
                    <div>
                        <h3 class="font-jakarta font-semibold text-white text-base">
                            @if ($incompleta)
                                Sincronización incompleta
                            @elseif ($conConflictos)
                                Sincronización completada con conflictos de vinculación por revisar
                            @else
                                Sincronización completada correctamente
                            @endif
                        </h3>
                        @if (! empty($syncResult['error_general']))
                            <p class="text-sm text-rose-300 mt-0.5">{{ $syncResult['error_general'] }}</p>
                        @else
                            <p class="text-xs text-slate-300 mt-0.5">
                                Páginas procesadas: {{ $syncResult['paginas_procesadas'] ?? 0 }}
                                · Vinculadas por ID de contacto: {{ $syncResult['vinculadas_por_id_contacto'] ?? 0 }}
                                · Vinculadas por correo único: {{ $syncResult['vinculadas_por_email'] ?? 0 }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-center shrink-0">
                    <div class="rounded-lg bg-slate-900/80 border border-slate-800 px-3 py-2">
                        <span class="block text-[11px] uppercase tracking-wider text-slate-400">Nuevas</span>
                        <span class="text-lg font-bold font-jakarta text-emerald-300">{{ $syncResult['creados'] ?? 0 }}</span>
                    </div>
                    <div class="rounded-lg bg-slate-900/80 border border-slate-800 px-3 py-2">
                        <span class="block text-[11px] uppercase tracking-wider text-slate-400">Actualizadas</span>
                        <span class="text-lg font-bold font-jakarta text-cyan-300">{{ $syncResult['actualizados'] ?? 0 }}</span>
                    </div>
                    <div class="rounded-lg bg-slate-900/80 border border-slate-800 px-3 py-2">
                        <span class="block text-[11px] uppercase tracking-wider text-slate-400">Vinculadas</span>
                        <span class="text-lg font-bold font-jakarta text-emerald-300">{{ $syncResult['vinculadas'] ?? 0 }}</span>
                    </div>
                    <div class="rounded-lg bg-slate-900/80 border border-slate-800 px-3 py-2">
                        <span class="block text-[11px] uppercase tracking-wider text-slate-400">Pendientes / Conflicto</span>
                        <span class="text-lg font-bold font-jakarta {{ (($syncResult['pendientes'] ?? 0) + ($syncResult['conflictos'] ?? 0)) > 0 ? 'text-amber-300' : 'text-slate-300' }}">
                            {{ ($syncResult['pendientes'] ?? 0) + ($syncResult['conflictos'] ?? 0) }}
                        </span>
                    </div>
                </div>
            </div>

            @if (! empty($syncResult['avisos']) && is_array($syncResult['avisos']))
                <ul class="mt-4 pt-3 border-t border-slate-800/80 space-y-1.5 text-xs">
                    @foreach ($syncResult['avisos'] as $aviso)
                        <li class="flex items-start gap-2 {{ ($aviso['tipo'] ?? '') === 'error' ? 'text-rose-300' : 'text-amber-200' }}">
                            <span class="font-semibold">{{ $aviso['referencia'] ?? 'Aviso' }}:</span>
                            <span>{{ $aviso['mensaje'] ?? '' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif

    {{-- ── Resumen de «Importar alumnos pendientes» ── --}}
    @if (is_array($importResult))
        @php
            $importError = ! empty($importResult['error_general']) || ($importResult['fallidos'] ?? 0) > 0;
            $importAlerta = ($importResult['conflictos'] ?? 0) > 0 || ($importResult['pendientes'] ?? 0) > 0;
        @endphp
        <section class="panel p-5 border {{ $importError ? 'border-rose-500/40 bg-rose-500/5' : ($importAlerta ? 'border-amber-500/40 bg-amber-500/5' : 'border-emerald-500/35 bg-emerald-500/5') }}"
                 role="status" aria-label="Resumen de importación de alumnos pendientes">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span class="w-10 h-10 shrink-0 rounded-xl flex items-center justify-center
                                 {{ $importError ? 'bg-rose-500/15 text-rose-300 border border-rose-400/30' : ($importAlerta ? 'bg-amber-500/15 text-amber-300 border border-amber-400/30' : 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/30') }}">
                        <x-icon :name="$importError || $importAlerta ? 'exclamation-triangle' : 'check-circle'" class="w-5 h-5" />
                    </span>
                    <div>
                        <h3 class="font-jakarta font-semibold text-white text-base">
                            @if ($importError)
                                Importación de alumnos pendientes con errores de consulta
                            @elseif (($importResult['conflictos'] ?? 0) > 0)
                                Importación procesada con conflictos por revisar
                            @elseif (($importResult['pendientes'] ?? 0) > 0)
                                Importación procesada con contactos pendientes por datos incompletos
                            @else
                                Importación de alumnos pendientes completada
                            @endif
                        </h3>
                        @if (! empty($importResult['error_general']))
                            <p class="text-sm text-rose-300 mt-0.5">{{ $importResult['error_general'] }}</p>
                        @else
                            <p class="text-xs text-slate-300 mt-0.5">
                                Curso: <span class="text-white font-medium">{{ $importResult['curso_nombre'] ?? ('#' . ($importResult['curso_filtro'] ?? '')) }}</span>
                                · Inscripciones evaluadas: {{ $importResult['inscripciones_evaluadas'] ?? 0 }}
                                · Contactos únicos consultados en API: {{ $importResult['contactos_consultados'] ?? 0 }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-center shrink-0">
                    <div class="rounded-lg bg-slate-900/80 border border-slate-800 px-3 py-2">
                        <span class="block text-[11px] uppercase tracking-wider text-slate-400">Personas creadas</span>
                        <span class="text-lg font-bold font-jakarta text-emerald-300">{{ $importResult['personas_creadas'] ?? 0 }}</span>
                    </div>
                    <div class="rounded-lg bg-slate-900/80 border border-slate-800 px-3 py-2">
                        <span class="block text-[11px] uppercase tracking-wider text-slate-400">Vinculaciones existentes</span>
                        <span class="text-lg font-bold font-jakarta text-cyan-300">{{ $importResult['vinculadas_existentes'] ?? 0 }}</span>
                    </div>
                    <div class="rounded-lg bg-slate-900/80 border border-slate-800 px-3 py-2">
                        <span class="block text-[11px] uppercase tracking-wider text-slate-400">Pendientes</span>
                        <span class="text-lg font-bold font-jakarta {{ ($importResult['pendientes'] ?? 0) > 0 ? 'text-amber-300' : 'text-slate-300' }}">
                            {{ $importResult['pendientes'] ?? 0 }}
                        </span>
                    </div>
                    <div class="rounded-lg bg-slate-900/80 border border-slate-800 px-3 py-2">
                        <span class="block text-[11px] uppercase tracking-wider text-slate-400">Conflictos</span>
                        <span class="text-lg font-bold font-jakarta {{ ($importResult['conflictos'] ?? 0) > 0 ? 'text-rose-300' : 'text-slate-300' }}">
                            {{ $importResult['conflictos'] ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>

            @if (! empty($importResult['avisos']) && is_array($importResult['avisos']))
                <ul class="mt-4 pt-3 border-t border-slate-800/80 space-y-1.5 text-xs">
                    @foreach ($importResult['avisos'] as $aviso)
                        <li class="flex items-start gap-2 {{ in_array($aviso['tipo'] ?? '', ['error', 'conflicto'], true) ? 'text-rose-300' : 'text-amber-200' }}">
                            <span class="font-semibold">{{ $aviso['referencia'] ?? 'Detalle' }}:</span>
                            <span>{{ $aviso['mensaje'] ?? '' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif

    {{-- ── Totales ── --}}
    @php
        $statItems = [
            ['label' => 'Total inscripciones',        'value' => $totales['total'],         'icon' => 'clipboard-list',       'color' => 'text-cyan-300',    'bg' => 'bg-nm-cyan/10 border-nm-cyan/25'],
            ['label' => 'Acceso activo (Systeme.io)', 'value' => $totales['acceso_activo'], 'icon' => 'check-circle',         'color' => 'text-emerald-300', 'bg' => 'bg-emerald-500/10 border-emerald-400/25'],
            ['label' => 'Vinculadas (Persona/Curso)', 'value' => $totales['vinculadas'],    'icon' => 'users',                'color' => 'text-emerald-300', 'bg' => 'bg-emerald-500/10 border-emerald-400/25'],
            ['label' => 'Pendientes de vinculación',  'value' => $totales['pendientes'],    'icon' => 'exclamation-triangle', 'color' => 'text-amber-300',   'bg' => 'bg-amber-500/10 border-amber-400/25'],
        ];
    @endphp
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($statItems as $s)
            <div class="stat-card flex items-center gap-4">
                <span class="w-11 h-11 shrink-0 rounded-xl border flex items-center justify-center {{ $s['bg'] }} {{ $s['color'] }}">
                    <x-icon :name="$s['icon']" class="w-6 h-6" />
                </span>
                <div class="min-w-0">
                    <p class="text-xs text-slate-400 font-medium uppercase tracking-wider truncate">{{ $s['label'] }}</p>
                    <p class="text-2xl sm:text-3xl font-bold font-jakarta text-white leading-tight">{{ number_format($s['value']) }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── Búsqueda y filtros ── --}}
    <form method="GET" action="{{ route('inscripciones.index') }}" id="filter-form" class="panel p-4 lg:p-5" role="search">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3 items-end">

            <div class="sm:col-span-2 lg:col-span-2">
                <label for="search-input" class="block text-xs font-medium text-slate-400 mb-1.5">Buscar</label>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
                    <input id="search-input" type="search" name="q" value="{{ request('q') }}"
                           placeholder="Correo, persona, curso o ID de Systeme…" class="nm-input-dark !pl-9" autocomplete="off">
                </div>
            </div>

            <div class="lg:col-span-2">
                <label for="filter-curso" class="block text-xs font-medium text-slate-400 mb-1.5">Curso / Programa</label>
                <select name="curso" id="filter-curso" class="nm-input-dark"
                        onchange="document.getElementById('filter-form').submit()">
                    <option value="">Todos los cursos</option>
                    @foreach ($programas as $prog)
                        @php
                            $valCurso = $prog->id_systeme_curso ?? (string) $prog->id;
                            $selectedCurso = (string) request('curso') === (string) $valCurso
                                || (string) request('curso') === (string) $prog->id;
                        @endphp
                        <option value="{{ $valCurso }}" @selected($selectedCurso)>
                            {{ $prog->nombre }} @if($prog->id_systeme_curso) (#{{ $prog->id_systeme_curso }}) @endif
                        </option>
                    @endforeach
                    <option value="sin_curso" @selected(request('curso') === 'sin_curso')>Curso sin sincronizar</option>
                </select>
            </div>

            <div>
                <label for="filter-acceso" class="block text-xs font-medium text-slate-400 mb-1.5">Estado de acceso</label>
                <select name="acceso" id="filter-acceso" class="nm-input-dark"
                        onchange="document.getElementById('filter-form').submit()">
                    <option value="">Todos</option>
                    <option value="activo" @selected(request('acceso') === 'activo')>Acceso activo</option>
                    <option value="inactivo" @selected(request('acceso') === 'inactivo')>Acceso inactivo</option>
                </select>
            </div>

            <div>
                <label for="filter-vinculacion" class="block text-xs font-medium text-slate-400 mb-1.5">Estado de vinculación</label>
                <select name="vinculacion" id="filter-vinculacion" class="nm-input-dark"
                        onchange="document.getElementById('filter-form').submit()">
                    <option value="">Todos</option>
                    <option value="vinculada" @selected(request('vinculacion') === 'vinculada')>Vinculadas</option>
                    <option value="pendiente" @selected(request('vinculacion') === 'pendiente')>Pendientes (todas)</option>
                    <option value="sin_persona" @selected(request('vinculacion') === 'sin_persona')>Sin persona en Laravel</option>
                    <option value="conflicto" @selected(request('vinculacion') === 'conflicto')>Conflicto de correo / ID</option>
                    <option value="sin_curso" @selected(request('vinculacion') === 'sin_curso')>Curso no vinculado</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" id="filter-submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-nm-cyan to-nm-cyan2 text-white text-sm font-semibold
                               px-4 py-2.5 rounded-lg hover:brightness-110 transition">
                    <x-icon name="search" class="w-4 h-4" />
                    Filtrar
                </button>
                @if (request()->hasAny(['q', 'curso', 'acceso', 'vinculacion']))
                    <a href="{{ route('inscripciones.index') }}" id="filter-clear"
                       class="inline-flex items-center justify-center px-3 rounded-lg border border-slate-600 text-slate-300
                              hover:border-nm-cyan hover:text-nm-cyan transition-colors"
                       title="Limpiar filtros" aria-label="Limpiar filtros">
                        <x-icon name="x-mark" class="w-4 h-4" />
                    </a>
                @endif
            </div>
        </div>

        <input type="hidden" name="sort" value="{{ request('sort', 'id_systeme_inscripcion') }}">
        <input type="hidden" name="dir"  value="{{ request('dir', 'desc') }}">
    </form>

    {{-- ── Tabla de la bandeja de inscripciones ── --}}
    <div class="panel overflow-hidden">
        @if ($inscripciones->isEmpty())
            <div class="text-center py-20 px-6">
                <span class="mx-auto mb-4 w-14 h-14 rounded-2xl bg-slate-800/70 border border-slate-700 flex items-center justify-center text-slate-400">
                    <x-icon name="clipboard-list" class="w-7 h-7" />
                </span>
                @if ($totales['total'] === 0)
                    <p class="font-semibold text-lg text-slate-200">Aún no hay inscripciones en la bandeja</p>
                    <p class="text-sm text-slate-400 mt-1 max-w-md mx-auto">
                        Utiliza el botón «Sincronizar desde Systeme.io» para importar las inscripciones y comprobar su vinculación con Personas y Programas.
                    </p>
                @else
                    <p class="font-semibold text-lg text-slate-200">Sin resultados para los filtros seleccionados</p>
                    <p class="text-sm text-slate-400 mt-1">Prueba ajustando el curso, el estado de acceso o el estado de vinculación.</p>
                    @if (request()->hasAny(['q', 'curso', 'acceso', 'vinculacion']))
                        <a href="{{ route('inscripciones.index') }}"
                           class="mt-5 inline-flex items-center gap-2 text-sm font-medium text-nm-cyan hover:text-cyan-300">
                            <x-icon name="x-mark" class="w-4 h-4" /> Limpiar filtros
                        </a>
                    @endif
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="nm-table" aria-label="Bandeja de inscripciones de Systeme.io">
                    <thead>
                        <tr>
                            <th scope="col">@include('inscripciones._sort_th', ['col' => 'id_systeme_inscripcion', 'label' => 'Inscripción / Contacto Systeme.io'])</th>
                            <th scope="col">Curso / Programa</th>
                            <th scope="col">@include('inscripciones._sort_th', ['col' => 'systeme_activo', 'label' => 'Acceso en Systeme.io'])</th>
                            <th scope="col">@include('inscripciones._sort_th', ['col' => 'vinculacion_estado', 'label' => 'Vinculación con Persona'])</th>
                            <th scope="col" class="hidden xl:table-cell">@include('inscripciones._sort_th', ['col' => 'sincronizado_en', 'label' => 'Sincronización / Fechas'])</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inscripciones as $item)
                            <tr>
                                {{-- 1. Inscripción y contacto --}}
                                <td>
                                    <p class="font-semibold text-white">{{ $item->email_contacto }}</p>
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-slate-400 mt-0.5">
                                        <span>Inscripción #{{ $item->id_systeme_inscripcion }}</span>
                                        <span>· Contacto #{{ $item->id_systeme_contacto }}</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        Alta contacto:
                                        {{ $item->contacto_registrado_en ? $item->contacto_registrado_en->format('d/m/Y H:i') : '—' }}
                                    </p>
                                </td>

                                {{-- 2. Curso / Programa --}}
                                <td>
                                    @if ($item->programa)
                                        <a href="{{ route('programas.show', $item->programa) }}"
                                           class="font-semibold text-nm-cyan hover:text-cyan-300 transition-colors">
                                            {{ $item->programa->nombre }}
                                        </a>
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            Curso Systeme #{{ $item->id_systeme_curso }}
                                        </p>
                                    @else
                                        <p class="font-semibold text-slate-200">
                                            {{ $item->curso_nombre_systeme ?? 'Curso no sincronizado' }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="badge badge-pendiente">Curso pendiente</span>
                                            <span class="text-xs text-slate-400">#{{ $item->id_systeme_curso }}</span>
                                        </div>
                                    @endif
                                </td>

                                {{-- 3. Estado y tipo de acceso en Systeme.io --}}
                                <td>
                                    @if ($item->systeme_activo)
                                        <span class="badge badge-activo">Acceso activo</span>
                                    @else
                                        <span class="badge badge-inactivo">Acceso inactivo</span>
                                    @endif
                                    <p class="text-xs text-slate-300 mt-1 font-medium">
                                        {{ $item->accessTypeLabel() }}
                                    </p>
                                    <p class="text-[11px] text-slate-500">{{ $item->access_type }}</p>
                                </td>

                                {{-- 4. Vinculación con Persona y motivo --}}
                                <td>
                                    @if ($item->persona)
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="badge badge-activo">Vinculada</span>
                                            @if ($item->vinculacionViaLabel())
                                                <span class="badge badge-default">{{ $item->vinculacionViaLabel() }}</span>
                                            @endif
                                        </div>
                                        <a href="{{ route('personas.show', $item->persona) }}"
                                           class="inline-flex items-center gap-1.5 font-semibold text-white hover:text-nm-cyan transition-colors mt-1.5">
                                            <span>{{ $item->persona->nombres }} {{ $item->persona->apellidos }}</span>
                                            <x-icon name="arrow-up-right" class="w-3.5 h-3.5 text-nm-cyan" />
                                        </a>
                                        @if ($item->vinculacion_motivo)
                                            <p class="text-xs text-amber-300/90 mt-1">{{ $item->vinculacion_motivo }}</p>
                                        @endif
                                    @elseif ($item->vinculacion_estado === \App\Models\InscripcionSysteme::VINCULACION_CONFLICTO)
                                        <span class="badge badge-error">Conflicto de vinculación</span>
                                        <p class="text-xs text-rose-300 mt-1 max-w-md">
                                            {{ $item->vinculacion_motivo ?? 'Conflicto al vincular con Persona.' }}
                                        </p>
                                    @else
                                        <span class="badge badge-pendiente">Vinculación pendiente</span>
                                        <p class="text-xs text-amber-200/90 mt-1 max-w-md">
                                            {{ $item->vinculacion_motivo ?? 'Pendiente de vincular con Persona.' }}
                                        </p>
                                    @endif
                                </td>

                                {{-- 5. Fechas separadas: última sincronización vs fecha de matrícula --}}
                                <td class="hidden xl:table-cell text-xs text-slate-400">
                                    <div>
                                        <span class="text-slate-500">Última sinc.:</span>
                                        <span class="text-slate-200 font-medium">
                                            {{ $item->sincronizado_en ? $item->sincronizado_en->format('d/m/Y H:i') : '—' }}
                                        </span>
                                    </div>
                                    <div class="mt-1">
                                        <span class="text-slate-500">Fecha matrícula:</span>
                                        @if ($item->fecha_matricula)
                                            <span class="text-slate-200">{{ $item->fecha_matricula->format('d/m/Y') }}</span>
                                        @else
                                            <span class="text-slate-500 italic">No informada por API</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- ── Paginación ── --}}
        @if ($inscripciones->hasPages())
            <nav class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-4 border-t border-slate-800" aria-label="Paginación">
                <p class="text-xs text-slate-400">
                    Mostrando
                    <span class="text-slate-200 font-medium">{{ $inscripciones->firstItem() }}</span>–<span class="text-slate-200 font-medium">{{ $inscripciones->lastItem() }}</span>
                    de <span class="text-slate-200 font-medium">{{ $inscripciones->total() }}</span> inscripciones
                </p>
                <div class="flex items-center gap-1 flex-wrap">
                    <a href="{{ $inscripciones->previousPageUrl() ?? '#' }}" aria-label="Página anterior"
                       class="pg-btn {{ $inscripciones->onFirstPage() ? 'disabled' : '' }}" @if ($inscripciones->onFirstPage()) aria-disabled="true" tabindex="-1" @endif>
                        <x-icon name="chevron-left" class="w-4 h-4" />
                    </a>
                    @foreach ($inscripciones->getUrlRange(max(1, $inscripciones->currentPage() - 2), min($inscripciones->lastPage(), $inscripciones->currentPage() + 2)) as $page => $url)
                        <a href="{{ $url }}" class="pg-btn {{ $page == $inscripciones->currentPage() ? 'active' : '' }}"
                           @if ($page == $inscripciones->currentPage()) aria-current="page" @endif>{{ $page }}</a>
                    @endforeach
                    <a href="{{ $inscripciones->nextPageUrl() ?? '#' }}" aria-label="Página siguiente"
                       class="pg-btn {{ ! $inscripciones->hasMorePages() ? 'disabled' : '' }}" @if (! $inscripciones->hasMorePages()) aria-disabled="true" tabindex="-1" @endif>
                        <x-icon name="chevron-right" class="w-4 h-4" />
                    </a>
                </div>
            </nav>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    const searchInput = document.getElementById('search-input');
    let debounceTimer;
    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => document.getElementById('filter-form').submit(), 600);
    });

    const syncForm = document.getElementById('sync-form');
    syncForm?.addEventListener('submit', () => {
        const btn = document.getElementById('sync-button');
        const lbl = document.getElementById('sync-button-label');
        if (btn && lbl) {
            btn.disabled = true;
            btn.classList.add('opacity-70', 'cursor-wait');
            lbl.textContent = 'Sincronizando…';
        }
    });

    const importForm = document.getElementById('import-alumnos-form');
    importForm?.addEventListener('submit', () => {
        const btn = document.getElementById('import-alumnos-button');
        const lbl = document.getElementById('import-alumnos-button-label');
        if (btn && lbl) {
            btn.disabled = true;
            btn.classList.add('opacity-70', 'cursor-wait');
            lbl.textContent = 'Importando alumnos…';
        }
    });
</script>
@endsection

