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

    .nm-table { width: 100%; min-width: 720px; border-collapse: collapse; }
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

    .course-thumb {
        width: 44px; height: 44px; border-radius: .65rem;
        display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
        font-size: .8rem; font-weight: 700; color: white; overflow: hidden;
        background: linear-gradient(135deg, #0e7490, #06b6d4);
        border: 1px solid rgba(6,182,212,.3);
    }

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
@endphp
<div class="space-y-6">

    {{-- ── Encabezado y botón de sincronización ── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl lg:text-3xl font-bold font-jakarta text-white">
                Programas <span class="text-gradient-cyan">/ Cursos</span>
            </h2>
            <p class="text-slate-400 text-sm mt-1">
                Catálogo sincronizado desde Systeme.io (solo lectura remota) y ficha académica local de NeuroManagement.
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

        @can('manage-programas')
            <form method="POST" action="{{ route('programas.sync') }}" id="sync-form" class="shrink-0">
                @csrf
                <button type="submit" id="sync-button"
                        class="admin-focus w-full sm:w-auto inline-flex items-center justify-center gap-2.5
                               bg-gradient-to-r from-nm-cyan to-nm-cyan2 text-white text-sm font-semibold
                               px-4 py-2.5 rounded-xl shadow-lg shadow-cyan-500/20 hover:brightness-110 transition">
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
            ['label' => 'Total cursos',             'value' => $totales['total'],                  'icon' => 'academic-cap',         'color' => 'text-cyan-300',    'bg' => 'bg-nm-cyan/10 border-nm-cyan/25'],
            ['label' => 'Activos en Systeme.io',    'value' => $totales['activos_systeme'],        'icon' => 'check-circle',         'color' => 'text-emerald-300', 'bg' => 'bg-emerald-500/10 border-emerald-400/25'],
            ['label' => 'Inactivos en Systeme.io',  'value' => $totales['inactivos_systeme'],      'icon' => 'pause-circle',         'color' => 'text-slate-300',   'bg' => 'bg-slate-500/10 border-slate-400/25'],
            ['label' => 'Estructuras pendientes',   'value' => $totales['estructuras_pendientes'], 'icon' => 'exclamation-triangle', 'color' => 'text-amber-300',   'bg' => 'bg-amber-500/10 border-amber-400/25'],
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
    <form method="GET" action="{{ route('programas.index') }}" id="filter-form" class="panel p-4 lg:p-5" role="search">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">

            <div class="sm:col-span-2 lg:col-span-3">
                <label for="search-input" class="block text-xs font-medium text-slate-400 mb-1.5">Buscar por nombre</label>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
                    <input id="search-input" type="search" name="q" value="{{ request('q') }}"
                           placeholder="Nombre del curso, instructor o categoría…" class="nm-input-dark !pl-9" autocomplete="off">
                </div>
            </div>

            <div>
                <label for="filter-estado" class="block text-xs font-medium text-slate-400 mb-1.5">Estado en Systeme.io</label>
                <select name="estado" id="filter-estado" class="nm-input-dark"
                        onchange="document.getElementById('filter-form').submit()">
                    <option value="">Todos los estados</option>
                    <option value="activo" @selected(request('estado') === 'activo')>Activo en Systeme.io</option>
                    <option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivo en Systeme.io</option>
                    <option value="pendiente_estructura" @selected(request('estado') === 'pendiente_estructura')>Estructura pendiente / con aviso</option>
                    <option value="no_presente" @selected(request('estado') === 'no_presente')>No presente en último listado</option>
                </select>
            </div>

            <div>
                <label for="filter-linea" class="block text-xs font-medium text-slate-400 mb-1.5">Tipo de programa</label>
                <select name="linea" id="filter-linea" class="nm-input-dark"
                        onchange="document.getElementById('filter-form').submit()">
                    <option value="">Todos los tipos</option>
                    @foreach (\App\Models\Programa::LINEAS as $key => $label)
                        <option value="{{ $key }}" @selected(request('linea') === $key)>{{ $label }}</option>
                    @endforeach
                    <option value="pendiente" @selected(request('linea') === 'pendiente')>Sin definir (pendiente)</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" id="filter-submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-nm-cyan to-nm-cyan2 text-white text-sm font-semibold
                               px-4 py-2.5 rounded-lg hover:brightness-110 transition">
                    <x-icon name="search" class="w-4 h-4" />
                    Filtrar
                </button>
                @if (request()->hasAny(['q', 'estado', 'linea']))
                    <a href="{{ route('programas.index') }}" id="filter-clear"
                       class="inline-flex items-center justify-center px-3 rounded-lg border border-slate-600 text-slate-300
                              hover:border-nm-cyan hover:text-nm-cyan transition-colors"
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
                            <th scope="col">@include('programas._sort_th', ['col' => 'nombre', 'label' => 'Curso / Programa'])</th>
                            <th scope="col">@include('programas._sort_th', ['col' => 'systeme_activo', 'label' => 'Estado en Systeme.io'])</th>
                            <th scope="col" class="hidden md:table-cell">Estructura (Módulos / Lecciones)</th>
                            <th scope="col" class="hidden lg:table-cell">@include('programas._sort_th', ['col' => 'linea', 'label' => 'Ficha local'])</th>
                            <th scope="col" class="hidden xl:table-cell">@include('programas._sort_th', ['col' => 'sincronizado_en', 'label' => 'Última sincronización'])</th>
                            <th scope="col" class="text-right">Acción</th>
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
                                        <div class="min-w-0">
                                            <p class="font-semibold text-white truncate">{{ $programa->nombre }}</p>
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-slate-400">
                                                @if ($programa->instructor)
                                                    <span>{{ $programa->instructor }}</span>
                                                @endif
                                                @if ($programa->id_systeme_curso)
                                                    <span class="text-slate-500">· ID Systeme #{{ $programa->id_systeme_curso }}</span>
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
                                        <span class="badge badge-pendiente">
                                            <x-icon name="exclamation-triangle" class="w-3.5 h-3.5" />
                                            Estructura pendiente: requiere paginación
                                        </span>
                                        @if ($estructuraConfirmada)
                                            <p class="text-xs text-slate-400 mt-1">
                                                Última estructura válida conservada: {{ $programa->modulos_count }} módulos · {{ $programa->lecciones_count }} lecciones
                                            </p>
                                        @else
                                            <p class="text-xs text-amber-300/80 mt-1">Sin conteo definitivo confirmado</p>
                                        @endif
                                    @elseif ($esErrorEst)
                                        <span class="badge badge-error">
                                            <x-icon name="exclamation-triangle" class="w-3.5 h-3.5" />
                                            Error al actualizar estructura
                                        </span>
                                        @if ($estructuraConfirmada)
                                            <p class="text-xs text-slate-400 mt-1">
                                                Última estructura válida conservada: {{ $programa->modulos_count }} módulos · {{ $programa->lecciones_count }} lecciones
                                            </p>
                                        @else
                                            <p class="text-xs text-rose-300/80 mt-1">Sin estructura confirmada</p>
                                        @endif
                                    @elseif ($estructuraConfirmada)
                                        <p class="text-sm font-medium text-slate-200">
                                            {{ $programa->modulos_count }} {{ $programa->modulos_count === 1 ? 'módulo' : 'módulos' }}
                                            ·
                                            {{ $programa->lecciones_count }} {{ $programa->lecciones_count === 1 ? 'lección' : 'lecciones' }}
                                        </p>
                                        <p class="text-xs text-emerald-400/90">Estructura completa confirmada</p>
                                    @else
                                        <span class="text-xs text-slate-500">Estructura aún no sincronizada</span>
                                    @endif
                                </td>

                                <td class="hidden lg:table-cell">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @if ($programa->linea)
                                            <span class="badge badge-default">{{ $programa->lineaLabel() }}</span>
                                        @endif
                                        @if ($programa->modalidad)
                                            <span class="badge badge-inactivo">{{ $programa->modalidadLabel() }}</span>
                                        @endif
                                        @if ($programa->horas)
                                            <span class="text-xs text-slate-300">{{ rtrim(rtrim(number_format((float) $programa->horas, 2, '.', ''), '0'), '.') }} h</span>
                                        @endif
                                        @if (! $programa->linea && ! $programa->modalidad && ! $programa->horas && ! $programa->categoria)
                                            <span class="text-xs text-amber-300/80">Ficha local pendiente</span>
                                        @endif
                                    </div>
                                    @if ($programa->categoria)
                                        <p class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]">{{ $programa->categoria }}</p>
                                    @endif
                                </td>

                                <td class="hidden xl:table-cell text-xs text-slate-400">
                                    @if ($programa->sincronizado_en)
                                        <span class="block text-slate-300">{{ $programa->sincronizado_en->format('d/m/Y H:i') }}</span>
                                    @else
                                        <span>—</span>
                                    @endif
                                </td>

                                <td class="text-right">
                                    <a href="{{ route('programas.show', $programa) }}"
                                       class="inline-flex items-center gap-1.5 text-xs font-medium text-nm-cyan hover:text-white
                                              px-3 py-1.5 rounded-lg border border-nm-cyan/35 hover:bg-nm-cyan/15 transition-colors"
                                       aria-label="Ver detalle de {{ $programa->nombre }}">
                                        <x-icon name="eye" class="w-4 h-4" />
                                        Detalle
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

