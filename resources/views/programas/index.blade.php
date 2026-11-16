@extends('layouts.admin')

@section('title', 'Programas / Cursos')
@section('page_title', 'Programas / Cursos')

@section('breadcrumbs')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Inicio', 'url' => route('dashboard')],
        ['label' => 'Programas / Cursos'],
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
    .nm-table { width: 100%; min-width: 720px; border-collapse: collapse; }
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
    .nm-table td { padding: .9rem 1rem; font-size: .875rem; vertical-align: middle; color: #cbd5e1; }

    .course-thumb {
        width: 36px; height: 36px; border-radius: .5rem;
        display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
        font-size: .75rem; font-weight: 600; color: #cbd5e1; overflow: hidden;
        background: #1e293b;
    }

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
    /* Etiquetas sin punto para datos de la ficha local */
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
@endphp
<div class="space-y-6">

    {{-- ── Encabezado y botón de sincronización ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-semibold font-jakarta text-white tracking-tight">Programas / Cursos</h1>
            <p class="text-sm text-slate-400 mt-1">
                Última sincronización:
                @if ($ultimaSincronizacion)
                    <span class="text-slate-200">{{ \Carbon\Carbon::parse($ultimaSincronizacion)->format('d/m/Y H:i') }}</span>
                @else
                    <span class="text-amber-300">aún no se ha ejecutado</span>
                @endif
            </p>
        </div>

        @can('manage-programas')
            <form method="POST" action="{{ route('programas.sync') }}" id="sync-form" class="shrink-0">
                @csrf
                <button type="submit" id="sync-button"
                        class="admin-focus w-full sm:w-auto inline-flex items-center justify-center gap-2
                               bg-nm-cyan2 hover:bg-nm-cyan text-white text-sm font-semibold
                               h-10 px-4 rounded-lg transition-colors">
                    <x-icon name="arrow-path" class="w-4 h-4" />
                    <span id="sync-button-label">Sincronizar desde Systeme.io</span>
                </button>
            </form>
        @endcan
    </div>

    {{-- ── Resumen de la última ejecución de sincronización ── --}}
    @if (is_array($syncResult))
        @php
            $hasIssues = ! empty($syncResult['error_general'])
                || ($syncResult['fallidos'] ?? 0) > 0
                || ($syncResult['estructuras_pendientes'] ?? 0) > 0
                || ($syncResult['estructuras_con_error'] ?? 0) > 0
                || empty($syncResult['listado_completo']);
        @endphp
        <section class="panel p-5 border {{ $hasIssues ? 'border-amber-500/40 bg-amber-500/5' : 'border-emerald-500/35 bg-emerald-500/5' }}"
                 role="status" aria-label="Resumen de sincronización">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span class="w-10 h-10 shrink-0 rounded-xl flex items-center justify-center
                                 {{ $hasIssues ? 'bg-amber-500/15 text-amber-300 border border-amber-400/30' : 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/30' }}">
                        <x-icon :name="$hasIssues ? 'exclamation-triangle' : 'check-circle'" class="w-5 h-5" />
                    </span>
                    <div>
                        <h3 class="font-jakarta font-semibold text-white text-base">
                            @if (! empty($syncResult['error_general']))
                                Sincronización interrumpida
                            @elseif ($hasIssues)
                                Sincronización finalizada con observaciones
                            @else
                                Sincronización completada correctamente
                            @endif
                        </h3>
                        @if (! empty($syncResult['error_general']))
                            <p class="text-sm text-rose-300 mt-0.5">{{ $syncResult['error_general'] }}</p>
                        @else
                            <p class="text-xs text-slate-300 mt-0.5">
                                Los datos locales de cada programa (tipo, modalidad, categoría, horas, objetivos y requisitos) se conservaron intactos.
                            </p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-center shrink-0">
                    <div class="rounded-lg bg-slate-900/80 border border-slate-800 px-3 py-2">
                        <span class="block text-[11px] uppercase tracking-wider text-slate-400">Creados</span>
                        <span class="text-lg font-bold font-jakarta text-emerald-300">{{ $syncResult['creados'] ?? 0 }}</span>
                    </div>
                    <div class="rounded-lg bg-slate-900/80 border border-slate-800 px-3 py-2">
                        <span class="block text-[11px] uppercase tracking-wider text-slate-400">Actualizados</span>
                        <span class="text-lg font-bold font-jakarta text-cyan-300">{{ $syncResult['actualizados'] ?? 0 }}</span>
                    </div>
                    <div class="rounded-lg bg-slate-900/80 border border-slate-800 px-3 py-2">
                        <span class="block text-[11px] uppercase tracking-wider text-slate-400">Fallidos</span>
                        <span class="text-lg font-bold font-jakarta {{ ($syncResult['fallidos'] ?? 0) > 0 ? 'text-rose-300' : 'text-slate-300' }}">
                            {{ $syncResult['fallidos'] ?? 0 }}
                        </span>
                    </div>
                    <div class="rounded-lg bg-slate-900/80 border border-slate-800 px-3 py-2">
                        <span class="block text-[11px] uppercase tracking-wider text-slate-400">Estructuras pendientes</span>
                        <span class="text-lg font-bold font-jakarta {{ (($syncResult['estructuras_pendientes'] ?? 0) + ($syncResult['estructuras_con_error'] ?? 0)) > 0 ? 'text-amber-300' : 'text-slate-300' }}">
                            {{ ($syncResult['estructuras_pendientes'] ?? 0) + ($syncResult['estructuras_con_error'] ?? 0) }}
                        </span>
                    </div>
                </div>
            </div>

            @if (! empty($syncResult['avisos']) && is_array($syncResult['avisos']))
                <ul class="mt-4 pt-3 border-t border-slate-800/80 space-y-1.5 text-xs">
                    @foreach ($syncResult['avisos'] as $aviso)
                        <li class="flex items-start gap-2 {{ ($aviso['tipo'] ?? '') === 'error' ? 'text-rose-300' : 'text-amber-200' }}">
                            <span class="font-semibold">{{ $aviso['curso'] ?? 'Curso' }}:</span>
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
            ['label' => 'Total cursos',             'value' => $totales['total'],                  'icon' => 'academic-cap',         'color' => 'text-cyan-300'],
            ['label' => 'Activos en Systeme.io',    'value' => $totales['activos_systeme'],        'icon' => 'check-circle',         'color' => 'text-emerald-300'],
            ['label' => 'Inactivos en Systeme.io',  'value' => $totales['inactivos_systeme'],      'icon' => 'pause-circle',         'color' => 'text-slate-400'],
            ['label' => 'Estructuras pendientes',   'value' => $totales['estructuras_pendientes'], 'icon' => 'exclamation-triangle', 'color' => 'text-amber-300'],
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
    <form method="GET" action="{{ route('programas.index') }}" id="filter-form" role="search">
        <div class="flex flex-col sm:flex-row sm:flex-wrap lg:flex-nowrap gap-2.5">

            <div class="sm:basis-full lg:basis-auto lg:flex-1 min-w-0">
                <label for="search-input" class="sr-only">Buscar por nombre, instructor o categoría</label>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
                    <input id="search-input" type="search" name="q" value="{{ request('q') }}"
                           placeholder="Buscar curso, instructor o categoría…" class="nm-input-dark !pl-9" autocomplete="off">
                </div>
            </div>

            <div class="sm:flex-1 lg:flex-none lg:w-56">
                <label for="filter-estado" class="sr-only">Estado en Systeme.io</label>
                <select name="estado" id="filter-estado" class="nm-input-dark"
                        onchange="document.getElementById('filter-form').submit()">
                    <option value="">Todos los estados</option>
                    <option value="activo" @selected(request('estado') === 'activo')>Activo en Systeme.io</option>
                    <option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivo en Systeme.io</option>
                    <option value="pendiente_estructura" @selected(request('estado') === 'pendiente_estructura')>Estructura pendiente / con aviso</option>
                    <option value="no_presente" @selected(request('estado') === 'no_presente')>No presente en último listado</option>
                </select>
            </div>

            <div class="sm:flex-1 lg:flex-none lg:w-48">
                <label for="filter-linea" class="sr-only">Tipo de programa</label>
                <select name="linea" id="filter-linea" class="nm-input-dark"
                        onchange="document.getElementById('filter-form').submit()">
                    <option value="">Todos los tipos</option>
                    @foreach (\App\Models\Programa::LINEAS as $key => $label)
                        <option value="{{ $key }}" @selected(request('linea') === $key)>{{ $label }}</option>
                    @endforeach
                    <option value="pendiente" @selected(request('linea') === 'pendiente')>Sin definir (pendiente)</option>
                </select>
            </div>

            <div class="flex gap-2 shrink-0">
                <button type="submit" id="filter-submit"
                        class="admin-focus flex-1 sm:flex-none inline-flex items-center justify-center gap-2 h-10 px-4 rounded-lg
                               border border-slate-700 text-slate-200 text-sm font-medium hover:bg-slate-800 transition-colors">
                    Filtrar
                </button>
                @if (request()->hasAny(['q', 'estado', 'linea']))
                    <a href="{{ route('programas.index') }}" id="filter-clear"
                       class="admin-focus inline-flex items-center justify-center w-10 h-10 rounded-lg border border-slate-700 text-slate-400
                              hover:bg-slate-800 hover:text-white transition-colors"
                       title="Limpiar filtros" aria-label="Limpiar filtros">
                        <x-icon name="x-mark" class="w-4 h-4" />
                    </a>
                @endif
            </div>
        </div>

        <input type="hidden" name="sort" value="{{ request('sort', 'nombre') }}">
        <input type="hidden" name="dir"  value="{{ request('dir', 'asc') }}">
    </form>

    {{-- ── Listado de programas ── --}}
    <div class="panel overflow-hidden">
        @if ($programas->isEmpty())
            <div class="text-center py-20 px-6">
                <span class="mx-auto mb-4 w-14 h-14 rounded-2xl bg-slate-800/70 border border-slate-700 flex items-center justify-center text-slate-400">
                    <x-icon name="academic-cap" class="w-7 h-7" />
                </span>
                @if ($totales['total'] === 0)
                    <p class="font-semibold text-lg text-slate-200">Aún no hay cursos sincronizados</p>
                    <p class="text-sm text-slate-400 mt-1 max-w-md mx-auto">
                        Utiliza el botón «Sincronizar desde Systeme.io» para importar los cursos, módulos y lecciones de la cuenta.
                    </p>
                @else
                    <p class="font-semibold text-lg text-slate-200">Sin resultados</p>
                    <p class="text-sm text-slate-400 mt-1">Prueba con otros filtros o términos de búsqueda.</p>
                    @if (request()->hasAny(['q', 'estado', 'linea']))
                        <a href="{{ route('programas.index') }}"
                           class="mt-5 inline-flex items-center gap-2 text-sm font-medium text-nm-cyan hover:text-cyan-300">
                            <x-icon name="x-mark" class="w-4 h-4" /> Limpiar filtros
                        </a>
                    @endif
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="nm-table" aria-label="Listado de programas y cursos">
                    <thead>
                        <tr>
                            <th scope="col">@include('programas._sort_th', ['col' => 'nombre', 'label' => 'Curso'])</th>
                            <th scope="col">@include('programas._sort_th', ['col' => 'systeme_activo', 'label' => 'Estado'])</th>
                            <th scope="col" class="hidden md:table-cell">Estructura</th>
                            <th scope="col" class="hidden lg:table-cell">@include('programas._sort_th', ['col' => 'linea', 'label' => 'Ficha local'])</th>
                            <th scope="col" class="hidden xl:table-cell">@include('programas._sort_th', ['col' => 'sincronizado_en', 'label' => 'Última sincronización'])</th>
                            <th scope="col" class="text-right"><span class="sr-only">Acción</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($programas as $programa)
                            @php
                                $initials = collect(preg_split('/\s+/', trim($programa->nombre)))
                                    ->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'PR';
                                $estructuraConfirmada = $programa->tieneEstructuraConfirmada();
                                $esPendientePag = $programa->estructura_estado === \App\Models\Programa::ESTRUCTURA_PENDIENTE;
                                $esErrorEst     = $programa->estructura_estado === \App\Models\Programa::ESTRUCTURA_ERROR;
                            @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="course-thumb" aria-hidden="true">
                                            @if ($programa->imagen_url)
                                                <img src="{{ $programa->imagen_url }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                            @else
                                                {{ $initials }}
                                            @endif
                                        </div>
                                        <div class="min-w-[12rem] max-w-[24rem]">
                                            <a href="{{ route('programas.show', $programa) }}"
                                               class="admin-focus block rounded text-[0.9375rem] font-semibold leading-snug text-white hover:text-cyan-200">{{ $programa->nombre }}</a>
                                            <div class="flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-xs text-slate-400 mt-0.5">
                                                @if ($programa->instructor)
                                                    <span>{{ $programa->instructor }}</span>
                                                @endif
                                                @if ($programa->instructor && $programa->id_systeme_curso)
                                                    <span aria-hidden="true">·</span>
                                                @endif
                                                @if ($programa->id_systeme_curso)
                                                    <span class="tabular-nums">#{{ $programa->id_systeme_curso }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if ($programa->systeme_presente === false)
                                        <span class="badge badge-pendiente" title="No apareció en el último listado de Systeme.io; se conserva en Laravel.">
                                            Ausente en listado
                                        </span>
                                    @elseif ($programa->systeme_activo === true)
                                        <span class="badge badge-activo">Activo</span>
                                    @elseif ($programa->systeme_activo === false)
                                        <span class="badge badge-inactivo">Inactivo</span>
                                    @else
                                        <span class="badge badge-default">Local</span>
                                    @endif
                                </td>

                                <td class="hidden md:table-cell">
                                    @if ($esPendientePag)
                                        <span class="badge badge-pendiente">Estructura pendiente: requiere paginación</span>
                                        @if ($estructuraConfirmada)
                                            <p class="text-xs text-slate-400 mt-1 tabular-nums">
                                                Se conserva la última válida: {{ $programa->modulos_count }} módulos · {{ $programa->lecciones_count }} lecciones
                                            </p>
                                        @else
                                            <p class="text-xs text-slate-400 mt-1">Sin conteo definitivo confirmado</p>
                                        @endif
                                    @elseif ($esErrorEst)
                                        <span class="badge badge-error">Error al actualizar estructura</span>
                                        @if ($estructuraConfirmada)
                                            <p class="text-xs text-slate-400 mt-1 tabular-nums">
                                                Se conserva la última válida: {{ $programa->modulos_count }} módulos · {{ $programa->lecciones_count }} lecciones
                                            </p>
                                        @else
                                            <p class="text-xs text-slate-400 mt-1">Sin estructura confirmada</p>
                                        @endif
                                    @elseif ($estructuraConfirmada)
                                        <span class="text-slate-200 tabular-nums">
                                            {{ $programa->modulos_count }} {{ $programa->modulos_count === 1 ? 'módulo' : 'módulos' }}
                                            ·
                                            {{ $programa->lecciones_count }} {{ $programa->lecciones_count === 1 ? 'lección' : 'lecciones' }}
                                        </span>
                                    @else
                                        <span class="text-slate-500">Aún no sincronizada</span>
                                    @endif
                                </td>

                                <td class="hidden lg:table-cell">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @if ($programa->linea)
                                            <span class="tag">{{ $programa->lineaLabel() }}</span>
                                        @endif
                                        @if ($programa->modalidad)
                                            <span class="tag">{{ $programa->modalidadLabel() }}</span>
                                        @endif
                                        @if ($programa->horas)
                                            <span class="text-xs text-slate-300 tabular-nums">{{ rtrim(rtrim(number_format((float) $programa->horas, 2, '.', ''), '0'), '.') }} h</span>
                                        @endif
                                        @if (! $programa->linea && ! $programa->modalidad && ! $programa->horas && ! $programa->categoria)
                                            <span class="text-slate-500">Pendiente</span>
                                        @endif
                                    </div>
                                    @if ($programa->categoria)
                                        <p class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]">{{ $programa->categoria }}</p>
                                    @endif
                                </td>

                                <td class="hidden xl:table-cell text-slate-400 tabular-nums whitespace-nowrap">
                                    {{ $programa->sincronizado_en ? $programa->sincronizado_en->format('d/m/Y H:i') : '—' }}
                                </td>

                                <td class="text-right whitespace-nowrap">
                                    <a href="{{ route('programas.show', $programa) }}"
                                       class="admin-focus inline-flex items-center gap-1 rounded text-sm font-medium text-slate-300 hover:text-white transition-colors"
                                       aria-label="Ver detalle de {{ $programa->nombre }}">
                                        Ver detalle
                                        <x-icon name="chevron-right" class="w-4 h-4" />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- ── Paginación ── --}}
        @if ($programas->hasPages())
            <nav class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-4 border-t border-slate-800" aria-label="Paginación">
                <p class="text-xs text-slate-400">
                    Mostrando
                    <span class="text-slate-200 font-medium">{{ $programas->firstItem() }}</span>–<span class="text-slate-200 font-medium">{{ $programas->lastItem() }}</span>
                    de <span class="text-slate-200 font-medium">{{ $programas->total() }}</span> programas
                </p>
                <div class="flex items-center gap-1 flex-wrap">
                    <a href="{{ $programas->previousPageUrl() ?? '#' }}" aria-label="Página anterior"
                       class="pg-btn {{ $programas->onFirstPage() ? 'disabled' : '' }}" @if ($programas->onFirstPage()) aria-disabled="true" tabindex="-1" @endif>
                        <x-icon name="chevron-left" class="w-4 h-4" />
                    </a>
                    @foreach ($programas->getUrlRange(max(1, $programas->currentPage() - 2), min($programas->lastPage(), $programas->currentPage() + 2)) as $page => $url)
                        <a href="{{ $url }}" class="pg-btn {{ $page == $programas->currentPage() ? 'active' : '' }}"
                           @if ($page == $programas->currentPage()) aria-current="page" @endif>{{ $page }}</a>
                    @endforeach
                    <a href="{{ $programas->nextPageUrl() ?? '#' }}" aria-label="Página siguiente"
                       class="pg-btn {{ ! $programas->hasMorePages() ? 'disabled' : '' }}" @if (! $programas->hasMorePages()) aria-disabled="true" tabindex="-1" @endif>
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
</script>
@endsection

