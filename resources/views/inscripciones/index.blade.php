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
    /* ── Tarjetas KPI ── */
    .stat-card {
        background: #0f172a;
        border: 1px solid #1e293b;
        border-radius: .75rem;
        padding: 1rem 1.125rem;
    }

    /* ── Campos de búsqueda y filtros ── */
    .nm-input-dark {
        width: 100%;
        height: 2.5rem;
        background: #0b1222;
        border: 1px solid #334155;
        border-radius: .5rem;
        color: #e2e8f0;
        padding: 0 .85rem;
        font-size: .875rem;
        font-family: 'Inter', sans-serif;
        transition: border-color .15s;
    }
    .nm-input-dark::placeholder { color: #64748b; }
    .nm-input-dark:hover { border-color: #475569; }
    .nm-input-dark:focus { outline: none; border-color: #06b6d4; }
    select.nm-input-dark {
        appearance: none; padding-right: 2.25rem; cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='m19.5 8.25-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right .7rem center; background-size: 1rem;
    }

    /* ── Tabla ── */
    .nm-table { width: 100%; min-width: 820px; border-collapse: collapse; }
    .nm-table thead tr { border-bottom: 1px solid #1e293b; }
    .nm-table th {
        padding: .75rem 1rem; text-align: left;
        font-size: .75rem; font-weight: 500;
        color: #94a3b8; white-space: nowrap;
    }
    .nm-table th a { color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: .3rem; border-radius: .25rem; }
    .nm-table th a:hover { color: #fff; }
    .nm-table tbody tr { border-bottom: 1px solid #172033; transition: background .15s; }
    .nm-table tbody tr:last-child { border-bottom: none; }
    .nm-table tbody tr:hover { background: #111b2e; }
    .nm-table td { padding: .9rem 1rem; font-size: .875rem; vertical-align: top; color: #cbd5e1; }

    /* ── Etiquetas de estado: punto de color + texto ── */
    .badge {
        display: inline-flex; align-items: center; gap: .4rem;
        font-size: .8125rem; font-weight: 500; white-space: nowrap; color: #cbd5e1;
    }
    .badge::before { content: ''; width: .45rem; height: .45rem; border-radius: 9999px; background: currentColor; flex-shrink: 0; }
    .badge-activo    { color: #34d399; }
    .badge-pendiente { color: #fbbf24; }
    .badge-inactivo  { color: #94a3b8; }
    .badge-error     { color: #fca5a5; }
    .badge-default   { color: #67e8f9; }
    /* Etiqueta neutra sin punto (p. ej. vía de vinculación) */
    .tag { display: inline-flex; align-items: center; font-size: .75rem; color: #cbd5e1; padding: .1rem .45rem; border-radius: .375rem; background: #1e293b; white-space: nowrap; }

    /* ── Paginación ── */
    .pg-btn {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 2rem; height: 2rem; padding: 0 .5rem;
        border-radius: .375rem; font-size: .8rem; font-weight: 500;
        border: 1px solid #334155; color: #cbd5e1; background: transparent;
        text-decoration: none; transition: background .15s, color .15s;
    }
    .pg-btn:hover  { background: #1e293b; color: #fff; }
    .pg-btn.active { background: #0891b2; color: #fff; border-color: #0891b2; }
    .pg-btn.disabled { opacity: .35; pointer-events: none; }

    .sort-asc::after  { content: ' ↑'; }
    .sort-desc::after { content: ' ↓'; }
</style>
@endsection

@section('content')
@php
    $syncResult = session('sync_result');
    $importResult = session('import_result');
    // Curso que procesa «Importar alumnos pendientes»: el del filtro «Curso» aplicado.
    // Con «Todos los cursos», «Curso sin sincronizar» o un valor que no corresponde a un programa,
    // se usa el curso predeterminado de la importación (no se amplía a todos los cursos).
    $cursoDefaultImport = \App\Services\Systeme\InscripcionSyncService::CURSO_DEFAULT_SYSTEME_ID;
    $cursoFiltro = trim((string) request('curso', ''));
    $programaFiltro = $cursoFiltro === '' || $cursoFiltro === 'sin_curso'
        ? null
        : $programas->first(fn ($p) => (string) ($p->id_systeme_curso ?? $p->id) === $cursoFiltro || (string) $p->id === $cursoFiltro);
    $cursoSeleccionadoImport = $programaFiltro
        ? (string) ($programaFiltro->id_systeme_curso ?? $programaFiltro->id)
        : $cursoDefaultImport;
    $cursoNombreImport = $programaFiltro?->nombre
        ?? $programas->first(fn ($p) => (string) $p->id_systeme_curso === $cursoDefaultImport)?->nombre
        ?? \App\Services\Systeme\InscripcionSyncService::CURSO_DEFAULT_NOMBRE;
@endphp
<div class="space-y-6">

    {{-- ── Encabezado y acciones administrativas ── --}}
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-semibold font-jakarta text-white tracking-tight">Bandeja de Inscripciones</h1>
            <p class="text-sm text-slate-400 mt-1">
                Última sincronización:
                @if ($ultimaSincronizacion)
                    <span class="text-slate-200">{{ \Carbon\Carbon::parse($ultimaSincronizacion)->format('d/m/Y H:i') }}</span>
                @else
                    <span class="text-amber-300">aún no se ha ejecutado</span>
                @endif
            </p>
        </div>

        @can('manage-inscripciones')
            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 shrink-0">
                <form method="POST" action="{{ route('inscripciones.import-alumnos') }}" id="import-alumnos-form">
                    @csrf
                    {{-- El curso proviene del filtro «Curso» aplicado (o del predeterminado). --}}
                    <input type="hidden" name="curso" id="import-curso" value="{{ $cursoSeleccionadoImport }}">
                    <button type="submit" id="import-alumnos-button"
                            title="Curso: {{ $cursoNombreImport }}"
                            class="admin-focus w-full sm:w-auto inline-flex items-center justify-center gap-2 h-10 px-4 rounded-lg
                                   border border-slate-700 text-slate-200 text-sm font-medium hover:bg-slate-800 transition-colors whitespace-nowrap">
                        <x-icon name="users" class="w-4 h-4 text-slate-400" />
                        <span id="import-alumnos-button-label">Importar alumnos pendientes</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('inscripciones.sync') }}" id="sync-form" class="shrink-0">
                    @csrf
                    <button type="submit" id="sync-button"
                            class="admin-focus w-full sm:w-auto inline-flex items-center justify-center gap-2
                                   bg-nm-cyan2 hover:bg-nm-cyan text-white text-sm font-semibold
                                   h-10 px-4 rounded-lg transition-colors whitespace-nowrap">
                        <x-icon name="arrow-path" class="w-4 h-4" />
                        <span id="sync-button-label">Sincronizar desde Systeme.io</span>
                    </button>
                </form>
            </div>
        @endcan
    </div>

    {{-- ── Aviso si falta ejecutar la migración ── --}}
    @if (! empty($migracionPendiente))
        <section class="panel p-4 border-amber-500/40 text-sm flex items-start gap-3">
            <x-icon name="exclamation-triangle" class="w-5 h-5 text-amber-300 shrink-0 mt-0.5" />
            <div>
                <p class="font-semibold text-white">Migración pendiente</p>
                <p class="text-xs text-slate-300 mt-0.5">
                    La tabla <code class="text-cyan-300">inscripcion_systeme</code> aún no existe en la base de datos activa.
                    Ejecuta <code class="text-cyan-300">php artisan migrate</code> para habilitar el almacenamiento de la bandeja.
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
        <section class="panel p-5 {{ $incompleta ? 'border-rose-500/40' : ($conConflictos ? 'border-amber-500/40' : 'border-emerald-500/35') }}"
                 role="status" aria-label="Resumen de sincronización de inscripciones">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-start gap-3">
                    <x-icon :name="$incompleta || $conConflictos ? 'exclamation-triangle' : 'check-circle'"
                            class="w-5 h-5 shrink-0 mt-0.5 {{ $incompleta ? 'text-rose-300' : ($conConflictos ? 'text-amber-300' : 'text-emerald-300') }}" />
                    <div>
                        <h2 class="font-semibold text-white text-base">
                            @if ($incompleta)
                                Sincronización incompleta
                            @elseif ($conConflictos)
                                Sincronización completada con conflictos de vinculación por revisar
                            @else
                                Sincronización completada correctamente
                            @endif
                        </h2>
                        @if (! empty($syncResult['error_general']))
                            <p class="text-sm text-rose-300 mt-0.5">{{ $syncResult['error_general'] }}</p>
                        @else
                            <p class="text-xs text-slate-400 mt-0.5">
                                Páginas procesadas: {{ $syncResult['paginas_procesadas'] ?? 0 }}
                                · Vinculadas por ID de contacto: {{ $syncResult['vinculadas_por_id_contacto'] ?? 0 }}
                                · Vinculadas por correo único: {{ $syncResult['vinculadas_por_email'] ?? 0 }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center shrink-0">
                    <div class="rounded-lg border border-slate-800 px-3 py-2">
                        <span class="block text-xs text-slate-400">Nuevas</span>
                        <span class="text-lg font-semibold text-white tabular-nums">{{ $syncResult['creados'] ?? 0 }}</span>
                    </div>
                    <div class="rounded-lg border border-slate-800 px-3 py-2">
                        <span class="block text-xs text-slate-400">Actualizadas</span>
                        <span class="text-lg font-semibold text-white tabular-nums">{{ $syncResult['actualizados'] ?? 0 }}</span>
                    </div>
                    <div class="rounded-lg border border-slate-800 px-3 py-2">
                        <span class="block text-xs text-slate-400">Vinculadas</span>
                        <span class="text-lg font-semibold text-white tabular-nums">{{ $syncResult['vinculadas'] ?? 0 }}</span>
                    </div>
                    <div class="rounded-lg border border-slate-800 px-3 py-2">
                        <span class="block text-xs text-slate-400">Pendientes / Conflicto</span>
                        <span class="text-lg font-semibold tabular-nums {{ (($syncResult['pendientes'] ?? 0) + ($syncResult['conflictos'] ?? 0)) > 0 ? 'text-amber-300' : 'text-white' }}">
                            {{ ($syncResult['pendientes'] ?? 0) + ($syncResult['conflictos'] ?? 0) }}
                        </span>
                    </div>
                </div>
            </div>

            @if (! empty($syncResult['avisos']) && is_array($syncResult['avisos']))
                <ul class="mt-4 pt-3 border-t border-slate-800 space-y-1.5 text-xs">
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
        <section class="panel p-5 {{ $importError ? 'border-rose-500/40' : ($importAlerta ? 'border-amber-500/40' : 'border-emerald-500/35') }}"
                 role="status" aria-label="Resumen de importación de alumnos pendientes">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-start gap-3">
                    <x-icon :name="$importError || $importAlerta ? 'exclamation-triangle' : 'check-circle'"
                            class="w-5 h-5 shrink-0 mt-0.5 {{ $importError ? 'text-rose-300' : ($importAlerta ? 'text-amber-300' : 'text-emerald-300') }}" />
                    <div>
                        <h2 class="font-semibold text-white text-base">
                            @if ($importError)
                                Importación de alumnos pendientes con errores de consulta
                            @elseif (($importResult['conflictos'] ?? 0) > 0)
                                Importación procesada con conflictos por revisar
                            @elseif (($importResult['pendientes'] ?? 0) > 0)
                                Importación procesada con contactos pendientes por datos incompletos
                            @else
                                Importación de alumnos pendientes completada
                            @endif
                        </h2>
                        @if (! empty($importResult['error_general']))
                            <p class="text-sm text-rose-300 mt-0.5">{{ $importResult['error_general'] }}</p>
                        @else
                            <p class="text-xs text-slate-400 mt-0.5">
                                Curso: <span class="text-white font-medium">{{ $importResult['curso_nombre'] ?? ('#' . ($importResult['curso_filtro'] ?? '')) }}</span>
                                · Inscripciones evaluadas: {{ $importResult['inscripciones_evaluadas'] ?? 0 }}
                                · Contactos únicos consultados en API: {{ $importResult['contactos_consultados'] ?? 0 }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center shrink-0">
                    <div class="rounded-lg border border-slate-800 px-3 py-2">
                        <span class="block text-xs text-slate-400">Personas creadas</span>
                        <span class="text-lg font-semibold text-white tabular-nums">{{ $importResult['personas_creadas'] ?? 0 }}</span>
                    </div>
                    <div class="rounded-lg border border-slate-800 px-3 py-2">
                        <span class="block text-xs text-slate-400">Vinculaciones existentes</span>
                        <span class="text-lg font-semibold text-white tabular-nums">{{ $importResult['vinculadas_existentes'] ?? 0 }}</span>
                    </div>
                    <div class="rounded-lg border border-slate-800 px-3 py-2">
                        <span class="block text-xs text-slate-400">Pendientes</span>
                        <span class="text-lg font-semibold tabular-nums {{ ($importResult['pendientes'] ?? 0) > 0 ? 'text-amber-300' : 'text-white' }}">
                            {{ $importResult['pendientes'] ?? 0 }}
                        </span>
                    </div>
                    <div class="rounded-lg border border-slate-800 px-3 py-2">
                        <span class="block text-xs text-slate-400">Conflictos</span>
                        <span class="text-lg font-semibold tabular-nums {{ ($importResult['conflictos'] ?? 0) > 0 ? 'text-rose-300' : 'text-white' }}">
                            {{ $importResult['conflictos'] ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>

            @if (! empty($importResult['avisos']) && is_array($importResult['avisos']))
                <ul class="mt-4 pt-3 border-t border-slate-800 space-y-1.5 text-xs">
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
            ['label' => 'Total inscripciones',        'value' => $totales['total'],         'icon' => 'clipboard-list',       'color' => 'text-cyan-300'],
            ['label' => 'Acceso activo (Systeme.io)', 'value' => $totales['acceso_activo'], 'icon' => 'check-circle',         'color' => 'text-emerald-300'],
            ['label' => 'Vinculadas (Persona/Curso)', 'value' => $totales['vinculadas'],    'icon' => 'users',                'color' => 'text-emerald-300'],
            ['label' => 'Pendientes de vinculación',  'value' => $totales['pendientes'],    'icon' => 'exclamation-triangle', 'color' => 'text-amber-300'],
        ];
    @endphp
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($statItems as $s)
            <div class="stat-card">
                <div class="flex items-start gap-2 min-w-0">
                    <x-icon :name="$s['icon']" class="w-4 h-4 mt-0.5 shrink-0 {{ $s['color'] }}" />
                    <p class="text-sm text-slate-400 leading-snug">{{ $s['label'] }}</p>
                </div>
                <p class="mt-2 font-jakarta text-2xl font-semibold text-white tabular-nums leading-none">
                    {{ number_format($s['value']) }}<span class="text-lg font-medium text-slate-400"> / {{ number_format($totales['total']) }}</span>
                </p>
            </div>
        @endforeach
    </div>

    {{-- ── Búsqueda y filtros ── --}}
    <form method="GET" action="{{ route('inscripciones.index') }}" id="filter-form" role="search">
        <div class="flex flex-col sm:flex-row sm:flex-wrap lg:flex-nowrap gap-2.5">

            <div class="sm:basis-full lg:basis-auto lg:flex-1 min-w-0">
                <label for="search-input" class="sr-only">Buscar por correo, persona, curso o ID de Systeme</label>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
                    <input id="search-input" type="search" name="q" value="{{ request('q') }}"
                           placeholder="Buscar correo, persona, curso o ID…" class="nm-input-dark !pl-9" autocomplete="off">
                </div>
            </div>

            <div class="sm:flex-1 lg:flex-none lg:w-56">
                <label for="filter-curso" class="sr-only">Curso / Programa</label>
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

            <div class="sm:flex-1 lg:flex-none lg:w-44">
                <label for="filter-acceso" class="sr-only">Estado de acceso</label>
                <select name="acceso" id="filter-acceso" class="nm-input-dark"
                        onchange="document.getElementById('filter-form').submit()">
                    <option value="">Todo acceso</option>
                    <option value="activo" @selected(request('acceso') === 'activo')>Acceso activo</option>
                    <option value="inactivo" @selected(request('acceso') === 'inactivo')>Acceso inactivo</option>
                </select>
            </div>

            <div class="sm:flex-1 lg:flex-none lg:w-52">
                <label for="filter-vinculacion" class="sr-only">Estado de vinculación</label>
                <select name="vinculacion" id="filter-vinculacion" class="nm-input-dark"
                        onchange="document.getElementById('filter-form').submit()">
                    <option value="">Toda vinculación</option>
                    <option value="vinculada" @selected(request('vinculacion') === 'vinculada')>Vinculadas</option>
                    <option value="pendiente" @selected(request('vinculacion') === 'pendiente')>Pendientes (todas)</option>
                    <option value="sin_persona" @selected(request('vinculacion') === 'sin_persona')>Sin persona en Laravel</option>
                    <option value="conflicto" @selected(request('vinculacion') === 'conflicto')>Conflicto de correo / ID</option>
                    <option value="sin_curso" @selected(request('vinculacion') === 'sin_curso')>Curso no vinculado</option>
                </select>
            </div>

            <div class="flex gap-2 shrink-0">
                <button type="submit" id="filter-submit"
                        class="admin-focus flex-1 sm:flex-none inline-flex items-center justify-center gap-2 h-10 px-4 rounded-lg
                               border border-slate-700 text-slate-200 text-sm font-medium hover:bg-slate-800 transition-colors">
                    Filtrar
                </button>
                @if (request()->hasAny(['q', 'curso', 'acceso', 'vinculacion']))
                    <a href="{{ route('inscripciones.index') }}" id="filter-clear"
                       class="admin-focus inline-flex items-center justify-center w-10 h-10 rounded-lg border border-slate-700 text-slate-400
                              hover:bg-slate-800 hover:text-white transition-colors"
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
            <div class="text-center py-16 px-6">
                <span class="mx-auto mb-4 w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400">
                    <x-icon name="clipboard-list" class="w-6 h-6" />
                </span>
                @if ($totales['total'] === 0)
                    <p class="font-semibold text-slate-200">Aún no hay inscripciones en la bandeja</p>
                    <p class="text-sm text-slate-400 mt-1 max-w-md mx-auto">
                        Utiliza el botón «Sincronizar desde Systeme.io» para importar las inscripciones y comprobar su vinculación con Personas y Programas.
                    </p>
                @else
                    <p class="font-semibold text-slate-200">Sin resultados para los filtros seleccionados</p>
                    <p class="text-sm text-slate-400 mt-1">Prueba ajustando el curso, el estado de acceso o el estado de vinculación.</p>
                    @if (request()->hasAny(['q', 'curso', 'acceso', 'vinculacion']))
                        <a href="{{ route('inscripciones.index') }}"
                           class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-slate-300 hover:text-white">
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
                            <th scope="col">@include('inscripciones._sort_th', ['col' => 'id_systeme_inscripcion', 'label' => 'Inscripción'])</th>
                            <th scope="col">Curso</th>
                            <th scope="col">@include('inscripciones._sort_th', ['col' => 'systeme_activo', 'label' => 'Acceso'])</th>
                            <th scope="col">@include('inscripciones._sort_th', ['col' => 'vinculacion_estado', 'label' => 'Vinculación'])</th>
                            <th scope="col" class="hidden xl:table-cell">@include('inscripciones._sort_th', ['col' => 'sincronizado_en', 'label' => 'Fechas'])</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inscripciones as $item)
                            <tr>
                                {{-- 1. Inscripción y contacto --}}
                                <td class="min-w-[15rem]">
                                    <p class="text-[0.9375rem] font-semibold text-white break-words">{{ $item->email_contacto }}</p>
                                </td>

                                {{-- 2. Curso / Programa: solo el nombre --}}
                                <td>
                                    @php
                                        $cursoNombre = $item->programa?->nombre ?? $item->curso_nombre_systeme;
                                    @endphp
                                    @if ($item->programa)
                                        <a href="{{ route('programas.show', $item->programa) }}"
                                           class="admin-focus rounded font-medium text-slate-100 hover:text-cyan-200 transition-colors">
                                            {{ $cursoNombre }}
                                        </a>
                                    @elseif ($cursoNombre)
                                        <span class="font-medium text-slate-200">{{ $cursoNombre }}</span>
                                    @else
                                        <span class="text-slate-400">Curso sin identificar</span>
                                    @endif
                                </td>

                                {{-- 3. Estado y tipo de acceso en Systeme.io (solo textos legibles) --}}
                                <td>
                                    @if ($item->systeme_activo)
                                        <span class="badge badge-activo">Acceso activo</span>
                                    @else
                                        <span class="badge badge-inactivo">Acceso inactivo</span>
                                    @endif
                                    <p class="text-xs text-slate-300 mt-1">{{ $item->accessTypeLabel() }}</p>
                                </td>

                                {{-- 4. Vinculación: solo el estado, con el mismo criterio de los filtros y KPI de la bandeja.
                                     Prioridad: conflicto → vinculada (persona y curso) → pendiente. --}}
                                <td>
                                    @if ($item->vinculacion_estado === \App\Models\InscripcionSysteme::VINCULACION_CONFLICTO)
                                        <span class="badge badge-error">Conflicto de vinculación</span>
                                    @elseif ($item->estaVinculada())
                                        <span class="badge badge-activo">Vinculada</span>
                                    @else
                                        <span class="badge badge-pendiente">Vinculación pendiente</span>
                                    @endif
                                </td>

                                {{-- 5. Fechas separadas: última sincronización vs fecha de matrícula --}}
                                <td class="hidden xl:table-cell text-xs text-slate-400">
                                    <div>
                                        Última sinc.:
                                        <span class="text-slate-200 tabular-nums">
                                            {{ $item->sincronizado_en ? $item->sincronizado_en->format('d/m/Y H:i') : '—' }}
                                        </span>
                                    </div>
                                    <div class="mt-1">
                                        Fecha matrícula:
                                        @if ($item->fecha_matricula)
                                            <span class="text-slate-200 tabular-nums">{{ $item->fecha_matricula->format('d/m/Y') }}</span>
                                        @else
                                            <span class="text-slate-500">No informada por API</span>
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
