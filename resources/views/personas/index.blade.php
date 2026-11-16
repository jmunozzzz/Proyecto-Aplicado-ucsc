@extends('layouts.admin')

@section('title', 'Personas / Alumnos')
@section('page_title', 'Personas / Alumnos')

@section('breadcrumbs')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Inicio', 'url' => route('dashboard')],
        ['label' => 'Personas / Alumnos'],
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
    select.nm-input-dark { appearance: none; padding-right: 2.25rem; cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='m19.5 8.25-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right .7rem center; background-size: 1rem; }

    /* ── Tabla ── */
    .nm-table { width: 100%; min-width: 640px; border-collapse: collapse; }
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

    .avatar {
        width: 36px; height: 36px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
        font-size: .75rem; font-weight: 600; color: #cbd5e1;
        background: #1e293b;
    }

    /* ── Etiquetas de estado: punto de color + texto ── */
    .badge {
        display: inline-flex; align-items: center; gap: .4rem;
        font-size: .8125rem; font-weight: 500; white-space: nowrap; color: #cbd5e1;
    }
    .badge::before { content: ''; width: .45rem; height: .45rem; border-radius: 9999px; background: currentColor; flex-shrink: 0; }
    .badge-activo    { color: #34d399; }
    .badge-lead      { color: #fbbf24; }
    .badge-inactivo  { color: #94a3b8; }
    .badge-default   { color: #67e8f9; }

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
<div class="space-y-6">

    {{-- ── Encabezado ── --}}
    <div class="min-w-0">
        <h1 class="text-2xl font-semibold font-jakarta text-white tracking-tight">Personas / Alumnos</h1>
        <p class="text-sm text-slate-400 mt-1">Directorio de contactos, leads y participantes.</p>
    </div>

    {{-- ── Totales ── --}}
    @php
        $statItems = [
            ['label' => 'Total personas', 'value' => $totales['total'],     'icon' => 'users',        'color' => 'text-cyan-300'],
            ['label' => 'Activos',        'value' => $totales['activos'],   'icon' => 'check-circle', 'color' => 'text-emerald-300'],
            ['label' => 'Leads',          'value' => $totales['leads'],     'icon' => 'user-plus',    'color' => 'text-amber-300'],
            ['label' => 'Inactivos',      'value' => $totales['inactivos'], 'icon' => 'pause-circle', 'color' => 'text-slate-400'],
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
    <form method="GET" action="{{ route('personas.index') }}" id="filter-form" role="search">
        <div class="flex flex-col sm:flex-row sm:flex-wrap lg:flex-nowrap gap-2.5">

            <div class="sm:basis-full lg:basis-auto lg:flex-1 min-w-0">
                <label for="search-input" class="sr-only">Buscar por nombre, email o documento</label>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
                    <input id="search-input" type="search" name="q" value="{{ request('q') }}"
                           placeholder="Buscar nombre, email o documento…" class="nm-input-dark !pl-9" autocomplete="off">
                </div>
            </div>

            <div class="sm:flex-1 lg:flex-none lg:w-48">
                <label for="filter-estado" class="sr-only">Estado</label>
                <select name="estado" id="filter-estado" class="nm-input-dark"
                        onchange="document.getElementById('filter-form').submit()">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $e)
                        <option value="{{ $e }}" @selected(request('estado') === $e)>{{ ucfirst($e) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:flex-1 lg:flex-none lg:w-48">
                <label for="filter-pais" class="sr-only">País</label>
                <select name="pais" id="filter-pais" class="nm-input-dark"
                        onchange="document.getElementById('filter-form').submit()">
                    <option value="">Todos los países</option>
                    @foreach ($paises as $p)
                        <option value="{{ $p }}" @selected(request('pais') === $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2 shrink-0">
                <button type="submit" id="filter-submit"
                        class="admin-focus flex-1 sm:flex-none inline-flex items-center justify-center gap-2 h-10 px-4 rounded-lg
                               border border-slate-700 text-slate-200 text-sm font-medium hover:bg-slate-800 transition-colors">
                    Buscar
                </button>
                @if (request()->hasAny(['q', 'estado', 'pais', 'genero']))
                    <a href="{{ route('personas.index') }}" id="filter-clear"
                       class="admin-focus inline-flex items-center justify-center w-10 h-10 rounded-lg border border-slate-700 text-slate-400
                              hover:bg-slate-800 hover:text-white transition-colors"
                       title="Limpiar filtros" aria-label="Limpiar filtros">
                        <x-icon name="x-mark" class="w-4 h-4" />
                    </a>
                @endif
            </div>
        </div>

        {{-- Mantener orden actual --}}
        <input type="hidden" name="sort" value="{{ request('sort', 'creado_en') }}">
        <input type="hidden" name="dir"  value="{{ request('dir', 'desc') }}">
    </form>

    {{-- ── Listado ── --}}
    <div class="panel overflow-hidden">
        @if ($personas->isEmpty())
            <div class="text-center py-16 px-6">
                <span class="mx-auto mb-4 w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400">
                    <x-icon name="search" class="w-6 h-6" />
                </span>
                <p class="font-semibold text-slate-200">Sin resultados</p>
                <p class="text-sm text-slate-400 mt-1">Prueba con otros filtros o términos de búsqueda.</p>
                @if (request()->hasAny(['q', 'estado', 'pais', 'genero']))
                    <a href="{{ route('personas.index') }}"
                       class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-slate-300 hover:text-white">
                        <x-icon name="x-mark" class="w-4 h-4" /> Limpiar filtros
                    </a>
                @endif
            </div>
        @else
            {{-- Scroll horizontal contenido en la tabla, no en la página --}}
            <div class="overflow-x-auto">
                <table class="nm-table" aria-label="Listado de personas">
                    <thead>
                        <tr>
                            <th scope="col">@include('personas._sort_th', ['col' => 'nombres', 'label' => 'Persona'])</th>
                            <th scope="col" class="hidden md:table-cell">@include('personas._sort_th', ['col' => 'email', 'label' => 'Email'])</th>
                            <th scope="col" class="hidden lg:table-cell">País</th>
                            <th scope="col" class="hidden xl:table-cell">@include('personas._sort_th', ['col' => 'creado_en', 'label' => 'Registro'])</th>
                            <th scope="col">@include('personas._sort_th', ['col' => 'estado', 'label' => 'Estado'])</th>
                            <th scope="col" class="text-right"><span class="sr-only">Acción</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($personas as $persona)
                            @php
                                $initials = strtoupper(mb_substr($persona->nombres, 0, 1) . mb_substr($persona->apellidos, 0, 1));
                                $badgeClass = match ($persona->estado) {
                                    'alumno_activo', 'activo' => 'badge-activo',
                                    'lead'                    => 'badge-lead',
                                    'inactivo'                => 'badge-inactivo',
                                    default                   => 'badge-default',
                                };
                                // Texto visible del estado; el valor interno se mantiene para filtros y conteos.
                                $estadoLabel = match ($persona->estado) {
                                    'alumno_activo' => 'Alumno activo',
                                    null            => 'sin estado',
                                    default         => $persona->estado,
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar" aria-hidden="true">{{ $initials }}</div>
                                        <div class="min-w-[10rem] max-w-[22rem]">
                                            <a href="{{ route('personas.show', $persona) }}"
                                               class="admin-focus block rounded text-[0.9375rem] font-semibold leading-snug text-white hover:text-cyan-200">{{ $persona->nombres }} {{ $persona->apellidos }}</a>
                                            <p class="text-xs text-slate-400 truncate md:hidden mt-0.5">{{ $persona->email }}</p>
                                            @if ($persona->numero_documento)
                                                <p class="text-xs text-slate-400 mt-0.5">{{ $persona->tipo_documento }}: {{ $persona->numero_documento }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell">
                                    <a href="mailto:{{ $persona->email }}" class="admin-focus rounded text-slate-300 hover:text-white transition-colors">{{ $persona->email }}</a>
                                </td>
                                <td class="hidden lg:table-cell">
                                    {{ $persona->pais ?? '—' }}@if ($persona->ciudad)<span class="text-slate-400">, {{ $persona->ciudad }}</span>@endif
                                </td>
                                <td class="hidden xl:table-cell text-slate-400 tabular-nums whitespace-nowrap">
                                    {{ $persona->creado_en ? \Carbon\Carbon::parse($persona->creado_en)->format('d/m/Y') : '—' }}
                                </td>
                                <td><span class="badge {{ $badgeClass }}">{{ $estadoLabel }}</span></td>
                                <td class="text-right whitespace-nowrap">
                                    <a href="{{ route('personas.show', $persona) }}"
                                       class="admin-focus inline-flex items-center gap-1 rounded text-sm font-medium text-slate-300 hover:text-white transition-colors"
                                       aria-label="Ver detalle de {{ $persona->nombres }} {{ $persona->apellidos }}">
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
        @if ($personas->hasPages())
            <nav class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-4 border-t border-slate-800" aria-label="Paginación">
                <p class="text-xs text-slate-400">
                    Mostrando
                    <span class="text-slate-200 font-medium">{{ $personas->firstItem() }}</span>–<span class="text-slate-200 font-medium">{{ $personas->lastItem() }}</span>
                    de <span class="text-slate-200 font-medium">{{ $personas->total() }}</span> personas
                </p>
                <div class="flex items-center gap-1 flex-wrap">
                    <a href="{{ $personas->previousPageUrl() ?? '#' }}" aria-label="Página anterior"
                       class="pg-btn {{ $personas->onFirstPage() ? 'disabled' : '' }}" @if ($personas->onFirstPage()) aria-disabled="true" tabindex="-1" @endif>
                        <x-icon name="chevron-left" class="w-4 h-4" />
                    </a>
                    @foreach ($personas->getUrlRange(max(1, $personas->currentPage() - 2), min($personas->lastPage(), $personas->currentPage() + 2)) as $page => $url)
                        <a href="{{ $url }}" class="pg-btn {{ $page == $personas->currentPage() ? 'active' : '' }}"
                           @if ($page == $personas->currentPage()) aria-current="page" @endif>{{ $page }}</a>
                    @endforeach
                    <a href="{{ $personas->nextPageUrl() ?? '#' }}" aria-label="Página siguiente"
                       class="pg-btn {{ ! $personas->hasMorePages() ? 'disabled' : '' }}" @if (! $personas->hasMorePages()) aria-disabled="true" tabindex="-1" @endif>
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
    // Búsqueda con debounce (envía al dejar de escribir)
    const searchInput = document.getElementById('search-input');
    let debounceTimer;
    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => document.getElementById('filter-form').submit(), 600);
    });
</script>
@endsection
