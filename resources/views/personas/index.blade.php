@extends('layouts.app')

@section('title', 'Personas / Alumnos — Neuro Management')

@section('styles')
<style>
    /* ── Personas module overrides ── */
    body { background: #0a1120; color: #e2e8f0; }

    /* Stats cards */
    .stat-card {
        background: linear-gradient(135deg, rgba(30,41,59,.85), rgba(15,23,42,.9));
        border: 1px solid rgba(6,182,212,.18);
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
        transition: border-color .25s, box-shadow .25s;
    }
    .stat-card:hover {
        border-color: rgba(6,182,212,.45);
        box-shadow: 0 0 24px rgba(6,182,212,.12);
    }

    /* Search & filter bar */
    .nm-input-dark {
        background: rgba(15,23,42,.9);
        border: 1px solid rgba(6,182,212,.2);
        border-radius: .5rem;
        color: #e2e8f0;
        padding: .55rem 1rem;
        font-size: .875rem;
        transition: border-color .2s, box-shadow .2s;
        font-family: 'Inter', sans-serif;
        width: 100%;
    }
    .nm-input-dark::placeholder { color: #64748b; }
    .nm-input-dark:focus {
        outline: none;
        border-color: #06b6d4;
        box-shadow: 0 0 0 3px rgba(6,182,212,.12);
    }

    /* Table */
    .nm-table { width: 100%; border-collapse: collapse; }
    .nm-table thead tr {
        background: rgba(6,182,212,.07);
        border-bottom: 1px solid rgba(6,182,212,.2);
    }
    .nm-table th {
        padding: .9rem 1rem;
        text-align: left;
        font-size: .75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #94a3b8;
        white-space: nowrap;
    }
    .nm-table th a { color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: .3rem; }
    .nm-table th a:hover { color: #06b6d4; }
    .nm-table tbody tr {
        border-bottom: 1px solid rgba(30,41,59,.8);
        transition: background .15s;
    }
    .nm-table tbody tr:hover { background: rgba(6,182,212,.04); }
    .nm-table td {
        padding: .85rem 1rem;
        font-size: .875rem;
        vertical-align: middle;
    }

    /* Avatar initials */
    .avatar {
        width: 36px; height: 36px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .75rem; font-weight: 700; flex-shrink: 0;
        background: linear-gradient(135deg, #0e7490, #06b6d4);
        color: white; letter-spacing: .02em;
    }

    /* Estado badge */
    .badge {
        display: inline-flex; align-items: center;
        padding: .2rem .65rem;
        border-radius: 9999px;
        font-size: .7rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: .05em;
    }
    .badge-activo    { background: rgba(16,185,129,.15); color: #34d399; border: 1px solid rgba(52,211,153,.25); }
    .badge-lead      { background: rgba(245,158,11,.12);  color: #fbbf24; border: 1px solid rgba(251,191,36,.2); }
    .badge-inactivo  { background: rgba(100,116,139,.12); color: #94a3b8; border: 1px solid rgba(148,163,184,.2); }
    .badge-default   { background: rgba(6,182,212,.1);    color: #67e8f9; border: 1px solid rgba(103,232,249,.2); }

    /* Pagination */
    .pg-btn {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 2rem; height: 2rem; padding: 0 .5rem;
        border-radius: .4rem; font-size: .8rem; font-weight: 500;
        border: 1px solid rgba(6,182,212,.2);
        color: #94a3b8; background: rgba(15,23,42,.8);
        text-decoration: none; transition: all .2s;
    }
    .pg-btn:hover  { background: rgba(6,182,212,.12); color: #06b6d4; border-color: #06b6d4; }
    .pg-btn.active { background: linear-gradient(135deg,#06b6d4,#0891b2); color: white; border-color: transparent; }
    .pg-btn.disabled { opacity: .35; pointer-events: none; }

    /* Sidebar panel */
    .panel {
        background: rgba(15,23,42,.95);
        border: 1px solid rgba(6,182,212,.15);
        border-radius: 1rem;
    }

    /* Sort arrow */
    .sort-asc::after  { content: ' ↑'; }
    .sort-desc::after { content: ' ↓'; }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-nm-darkest px-4 pt-24 pb-8 lg:px-8">

    {{-- ── Header ── --}}
    <div class="max-w-screen-xl mx-auto mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-nm-cyan text-sm font-semibold uppercase tracking-widest mb-1">Módulo</p>
                <h1 class="text-2xl lg:text-3xl font-bold font-jakarta text-white">
                    Personas <span class="text-gradient-cyan">/ Alumnos</span>
                </h1>
                <p class="text-slate-400 text-sm mt-1">Directorio completo de contactos, leads y participantes</p>
            </div>
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-nm-cyan transition-colors">
                ← Volver al inicio
            </a>
        </div>
    </div>

    <div class="max-w-screen-xl mx-auto space-y-6">

        {{-- ── Stats Cards ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $statItems = [
                    ['label'=>'Total personas',  'value'=>$totales['total'],    'icon'=>'👥', 'color'=>'text-nm-cyan'],
                    ['label'=>'Activos',          'value'=>$totales['activos'],  'icon'=>'✅', 'color'=>'text-emerald-400'],
                    ['label'=>'Leads',            'value'=>$totales['leads'],    'icon'=>'🎯', 'color'=>'text-amber-400'],
                    ['label'=>'Inactivos',        'value'=>$totales['inactivos'],'icon'=>'💤', 'color'=>'text-slate-400'],
                ];
            @endphp
            @foreach($statItems as $s)
            <div class="stat-card fade-in-up">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xl">{{ $s['icon'] }}</span>
                    <span class="text-xs text-slate-500 uppercase tracking-widest">{{ $s['label'] }}</span>
                </div>
                <p class="text-3xl font-bold font-jakarta {{ $s['color'] }}">
                    {{ number_format($s['value']) }}
                </p>
            </div>
            @endforeach
        </div>

        {{-- ── Search & Filters ── --}}
        <form method="GET" action="{{ route('personas.index') }}" id="filter-form"
              class="panel p-4 lg:p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">

                {{-- Búsqueda texto --}}
                <div class="lg:col-span-2 relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">🔍</span>
                    <input id="search-input"
                           type="text" name="q"
                           value="{{ request('q') }}"
                           placeholder="Nombre, email o documento…"
                           class="nm-input-dark pl-9"
                           autocomplete="off">
                </div>

                {{-- Estado --}}
                <div>
                    <select name="estado" id="filter-estado" class="nm-input-dark"
                            onchange="document.getElementById('filter-form').submit()">
                        <option value="">Todos los estados</option>
                        @foreach($estados as $e)
                            <option value="{{ $e }}" @selected(request('estado') === $e)>
                                {{ ucfirst($e) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- País --}}
                <div>
                    <select name="pais" id="filter-pais" class="nm-input-dark"
                            onchange="document.getElementById('filter-form').submit()">
                        <option value="">Todos los países</option>
                        @foreach($paises as $p)
                            <option value="{{ $p }}" @selected(request('pais') === $p)>
                                {{ $p }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Botones --}}
                <div class="flex gap-2">
                    <button type="submit"
                            class="flex-1 bg-gradient-to-r from-nm-cyan to-nm-cyan2 text-white text-sm font-semibold
                                   px-4 py-2 rounded-lg hover:opacity-90 transition-opacity">
                        Buscar
                    </button>
                    @if(request()->hasAny(['q','estado','pais','genero']))
                    <a href="{{ route('personas.index') }}"
                       class="px-3 py-2 rounded-lg border border-slate-600 text-slate-400 text-sm
                              hover:border-nm-cyan hover:text-nm-cyan transition-colors">
                        ✕
                    </a>
                    @endif
                </div>
            </div>

            {{-- Mantener sort en el form --}}
            <input type="hidden" name="sort" value="{{ request('sort','creado_en') }}">
            <input type="hidden" name="dir"  value="{{ request('dir','desc') }}">
        </form>

        {{-- ── Tabla ── --}}
        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">

                @if($personas->isEmpty())
                    <div class="text-center py-20 text-slate-500">
                        <p class="text-5xl mb-4">🔍</p>
                        <p class="font-semibold text-lg text-slate-400">Sin resultados</p>
                        <p class="text-sm mt-1">Prueba con otros filtros o términos de búsqueda.</p>
                    </div>
                @else
                <table class="nm-table" aria-label="Listado de personas">
                    <thead>
                        <tr>
                            <th>
                                @include('personas._sort_th', ['col'=>'nombres', 'label'=>'Persona'])
                            </th>
                            <th class="hidden md:table-cell">
                                @include('personas._sort_th', ['col'=>'email', 'label'=>'Email'])
                            </th>
                            <th class="hidden lg:table-cell">País</th>
                            <th class="hidden xl:table-cell">
                                @include('personas._sort_th', ['col'=>'creado_en', 'label'=>'Registro'])
                            </th>
                            <th>
                                @include('personas._sort_th', ['col'=>'estado', 'label'=>'Estado'])
                            </th>
                            <th class="text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($personas as $persona)
                        @php
                            $initials = strtoupper(mb_substr($persona->nombres,0,1).mb_substr($persona->apellidos,0,1));
                            $badgeClass = match($persona->estado) {
                                'activo'   => 'badge-activo',
                                'lead'     => 'badge-lead',
                                'inactivo' => 'badge-inactivo',
                                default    => 'badge-default',
                            };
                        @endphp
                        <tr>
                            {{-- Persona --}}
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar">{{ $initials }}</div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-white truncate">
                                            {{ $persona->nombres }} {{ $persona->apellidos }}
                                        </p>
                                        <p class="text-xs text-slate-500 truncate md:hidden">
                                            {{ $persona->email }}
                                        </p>
                                        @if($persona->numero_documento)
                                        <p class="text-xs text-slate-600">
                                            {{ $persona->tipo_documento }}: {{ $persona->numero_documento }}
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Email --}}
                            <td class="hidden md:table-cell text-slate-300">
                                <a href="mailto:{{ $persona->email }}"
                                   class="hover:text-nm-cyan transition-colors">
                                    {{ $persona->email }}
                                </a>
                            </td>

                            {{-- País --}}
                            <td class="hidden lg:table-cell text-slate-400 text-sm">
                                {{ $persona->pais ?? '—' }}
                                @if($persona->ciudad)
                                    <span class="text-slate-600">, {{ $persona->ciudad }}</span>
                                @endif
                            </td>

                            {{-- Registro --}}
                            <td class="hidden xl:table-cell text-slate-500 text-xs">
                                {{ $persona->creado_en ? \Carbon\Carbon::parse($persona->creado_en)->format('d/m/Y') : '—' }}
                            </td>

                            {{-- Estado --}}
                            <td>
                                <span class="badge {{ $badgeClass }}">
                                    {{ $persona->estado ?? 'sin estado' }}
                                </span>
                            </td>

                            {{-- Acción --}}
                            <td class="text-right">
                                <a href="{{ route('personas.show', $persona) }}"
                                   class="inline-flex items-center gap-1 text-xs font-medium text-nm-cyan
                                          hover:text-white transition-colors px-3 py-1.5 rounded-lg
                                          border border-nm-cyan/30 hover:bg-nm-cyan/10">
                                    Ver →
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            {{-- ── Paginación ── --}}
            @if($personas->hasPages())
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-4
                        border-t border-slate-800">
                <p class="text-xs text-slate-500">
                    Mostrando
                    <span class="text-slate-300 font-medium">{{ $personas->firstItem() }}</span>–<span class="text-slate-300 font-medium">{{ $personas->lastItem() }}</span>
                    de <span class="text-slate-300 font-medium">{{ $personas->total() }}</span> personas
                </p>
                <div class="flex items-center gap-1 flex-wrap">
                    {{-- Anterior --}}
                    <a href="{{ $personas->previousPageUrl() ?? '#' }}"
                       class="pg-btn {{ $personas->onFirstPage() ? 'disabled' : '' }}">‹</a>

                    {{-- Páginas --}}
                    @foreach($personas->getUrlRange(max(1,$personas->currentPage()-2), min($personas->lastPage(),$personas->currentPage()+2)) as $page => $url)
                        <a href="{{ $url }}" class="pg-btn {{ $page == $personas->currentPage() ? 'active' : '' }}">
                            {{ $page }}
                        </a>
                    @endforeach

                    {{-- Siguiente --}}
                    <a href="{{ $personas->nextPageUrl() ?? '#' }}"
                       class="pg-btn {{ !$personas->hasMorePages() ? 'disabled' : '' }}">›</a>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    // Búsqueda con debounce (submit al dejar de escribir)
    const searchInput = document.getElementById('search-input');
    let debounceTimer;
    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            document.getElementById('filter-form').submit();
        }, 600);
    });
</script>
@endsection
