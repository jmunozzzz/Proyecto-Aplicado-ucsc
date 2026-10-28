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
    /* ── Personas: tarjetas de totales ── */
    .stat-card {
        background: linear-gradient(135deg, #111a2e, #0f172a);
        border: 1px solid rgba(51,65,85,.65);
        border-radius: 1rem;
        padding: 1.15rem 1.25rem;
        transition: border-color .2s, box-shadow .2s;
    }
    .stat-card:hover { border-color: rgba(6,182,212,.4); box-shadow: 0 0 24px rgba(6,182,212,.08); }

    /* ── Campos de búsqueda/filtros ── */
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
    select.nm-input-dark { appearance: none; padding-right: 2.25rem; cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='m19.5 8.25-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right .7rem center; background-size: 1rem; }

    /* ── Tabla ── */
    .nm-table { width: 100%; min-width: 640px; border-collapse: collapse; }
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

    .avatar {
        width: 36px; height: 36px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
        font-size: .75rem; font-weight: 700; letter-spacing: .02em; color: white;
        background: linear-gradient(135deg, #0e7490, #06b6d4);
    }

    .badge {
        display: inline-flex; align-items: center;
        padding: .2rem .65rem; border-radius: 9999px;
        font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; white-space: nowrap;
    }
    .badge-activo    { background: rgba(16,185,129,.15); color: #34d399; border: 1px solid rgba(52,211,153,.3); }
    .badge-lead      { background: rgba(245,158,11,.13); color: #fbbf24; border: 1px solid rgba(251,191,36,.28); }
    .badge-inactivo  { background: rgba(100,116,139,.15); color: #cbd5e1; border: 1px solid rgba(148,163,184,.28); }
    .badge-default   { background: rgba(6,182,212,.12);  color: #67e8f9; border: 1px solid rgba(103,232,249,.28); }

    /* ── Paginación ── */
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
<div class="space-y-6">

    {{-- ── Encabezado ── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <h2 class="text-2xl lg:text-3xl font-bold font-jakarta text-white">
                Personas <span class="text-gradient-cyan">/ Alumnos</span>
            </h2>
            <p class="text-slate-400 text-sm mt-1">Directorio completo de contactos, leads y participantes.</p>
        </div>
    </div>

    {{-- ── Totales ── --}}
    @php
        $statItems = [
            ['label' => 'Total personas', 'value' => $totales['total'],     'icon' => 'users',        'color' => 'text-cyan-300',    'bg' => 'bg-nm-cyan/10 border-nm-cyan/25'],
            ['label' => 'Activos',        'value' => $totales['activos'],   'icon' => 'check-circle', 'color' => 'text-emerald-300', 'bg' => 'bg-emerald-500/10 border-emerald-400/25'],
            ['label' => 'Leads',          'value' => $totales['leads'],     'icon' => 'user-plus',    'color' => 'text-amber-300',   'bg' => 'bg-amber-500/10 border-amber-400/25'],
            ['label' => 'Inactivos',      'value' => $totales['inactivos'], 'icon' => 'pause-circle', 'color' => 'text-slate-300',   'bg' => 'bg-slate-500/10 border-slate-400/25'],
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
    <form method="GET" action="{{ route('personas.index') }}" id="filter-form" class="panel p-4 lg:p-5" role="search">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">

            <div class="sm:col-span-2 lg:col-span-3">
                <label for="search-input" class="block text-xs font-medium text-slate-400 mb-1.5">Buscar</label>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
                    <input id="search-input" type="search" name="q" value="{{ request('q') }}"
                           placeholder="Nombre, email o documento…" class="nm-input-dark !pl-9" autocomplete="off">
                </div>
            </div>

            <div>
                <label for="filter-estado" class="block text-xs font-medium text-slate-400 mb-1.5">Estado</label>
                <select name="estado" id="filter-estado" class="nm-input-dark"
                        onchange="document.getElementById('filter-form').submit()">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $e)
                        <option value="{{ $e }}" @selected(request('estado') === $e)>{{ ucfirst($e) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter-pais" class="block text-xs font-medium text-slate-400 mb-1.5">País</label>
                <select name="pais" id="filter-pais" class="nm-input-dark"
                        onchange="document.getElementById('filter-form').submit()">
                    <option value="">Todos los países</option>
                    @foreach ($paises as $p)
                        <option value="{{ $p }}" @selected(request('pais') === $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" id="filter-submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-nm-cyan to-nm-cyan2 text-white text-sm font-semibold
                               px-4 py-2.5 rounded-lg hover:brightness-110 transition">
                    <x-icon name="search" class="w-4 h-4" />
                    Buscar
                </button>
                @if (request()->hasAny(['q', 'estado', 'pais', 'genero']))
                    <a href="{{ route('personas.index') }}" id="filter-clear"
                       class="inline-flex items-center justify-center px-3 rounded-lg border border-slate-600 text-slate-300
                              hover:border-nm-cyan hover:text-nm-cyan transition-colors"
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
            <div class="text-center py-20 px-6">
                <span class="mx-auto mb-4 w-14 h-14 rounded-2xl bg-slate-800/70 border border-slate-700 flex items-center justify-center text-slate-400">
                    <x-icon name="search" class="w-7 h-7" />
                </span>
                <p class="font-semibold text-lg text-slate-200">Sin resultados</p>
                <p class="text-sm text-slate-400 mt-1">Prueba con otros filtros o términos de búsqueda.</p>
                @if (request()->hasAny(['q', 'estado', 'pais', 'genero']))
                    <a href="{{ route('personas.index') }}"
                       class="mt-5 inline-flex items-center gap-2 text-sm font-medium text-nm-cyan hover:text-cyan-300">
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
                            <th scope="col" class="text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($personas as $persona)
                            @php
                                $initials = strtoupper(mb_substr($persona->nombres, 0, 1) . mb_substr($persona->apellidos, 0, 1));
                                $badgeClass = match ($persona->estado) {
                                    'activo'   => 'badge-activo',
                                    'lead'     => 'badge-lead',
                                    'inactivo' => 'badge-inactivo',
                                    default    => 'badge-default',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar" aria-hidden="true">{{ $initials }}</div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-white truncate">{{ $persona->nombres }} {{ $persona->apellidos }}</p>
                                            <p class="text-xs text-slate-400 truncate md:hidden">{{ $persona->email }}</p>
                                            @if ($persona->numero_documento)
                                                <p class="text-xs text-slate-500">{{ $persona->tipo_documento }}: {{ $persona->numero_documento }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell text-slate-300">
                                    <a href="mailto:{{ $persona->email }}" class="hover:text-nm-cyan transition-colors rounded">{{ $persona->email }}</a>
                                </td>
                                <td class="hidden lg:table-cell text-slate-300 text-sm">
                                    {{ $persona->pais ?? '—' }}@if ($persona->ciudad)<span class="text-slate-500">, {{ $persona->ciudad }}</span>@endif
                                </td>
                                <td class="hidden xl:table-cell text-slate-400 text-xs">
                                    {{ $persona->creado_en ? \Carbon\Carbon::parse($persona->creado_en)->format('d/m/Y') : '—' }}
                                </td>
                                <td><span class="badge {{ $badgeClass }}">{{ $persona->estado ?? 'sin estado' }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('personas.show', $persona) }}"
                                       class="inline-flex items-center gap-1.5 text-xs font-medium text-nm-cyan hover:text-white
                                              px-3 py-1.5 rounded-lg border border-nm-cyan/35 hover:bg-nm-cyan/15 transition-colors"
                                       aria-label="Ver detalle de {{ $persona->nombres }} {{ $persona->apellidos }}">
                                        <x-icon name="eye" class="w-4 h-4" />
                                        Ver
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
