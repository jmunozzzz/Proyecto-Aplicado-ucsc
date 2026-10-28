@extends('layouts.admin')

@section('title', $persona->nombres . ' ' . $persona->apellidos . ' | Personas')
@section('page_title', 'Personas / Alumnos')

@section('breadcrumbs')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Inicio', 'url' => route('dashboard')],
        ['label' => 'Personas / Alumnos', 'url' => route('personas.index')],
        ['label' => $persona->nombres . ' ' . $persona->apellidos],
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
        min-width: 8.5rem;
        flex-shrink: 0;
    }
    .info-value {
        font-size: .875rem;
        color: #f1f5f9;
        word-break: break-word;
    }

    .avatar-lg {
        width: 64px;
        height: 64px;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        font-weight: 800;
        background: linear-gradient(135deg, #0e7490, #06b6d4);
        color: white;
        flex-shrink: 0;
        box-shadow: 0 0 24px rgba(6,182,212,.25);
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: .25rem .75rem;
        border-radius: 9999px;
        font-size: .7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        white-space: nowrap;
    }
    .badge-activo    { background: rgba(16,185,129,.15); color: #34d399; border: 1px solid rgba(52,211,153,.3); }
    .badge-lead      { background: rgba(245,158,11,.13); color: #fbbf24; border: 1px solid rgba(251,191,36,.28); }
    .badge-inactivo  { background: rgba(100,116,139,.15); color: #cbd5e1; border: 1px solid rgba(148,163,184,.28); }
    .badge-default   { background: rgba(6,182,212,.12);  color: #67e8f9; border: 1px solid rgba(103,232,249,.28); }

    /* Inscripciones table */
    .ins-table { width: 100%; min-width: 620px; border-collapse: collapse; }
    .ins-table th {
        padding: .75rem 1rem;
        text-align: left;
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #94a3b8;
        border-bottom: 1px solid rgba(51,65,85,.7);
        white-space: nowrap;
    }
    .ins-table td {
        padding: .85rem 1rem;
        font-size: .85rem;
        border-bottom: 1px solid rgba(30,41,59,.8);
        vertical-align: middle;
    }
    .ins-table tr:last-child td { border-bottom: none; }
    .ins-table tr:hover td { background: rgba(6,182,212,.04); }

    /* Progress bar */
    .progress-bar { height: 6px; border-radius: 3px; background: rgba(30,41,59,.9); overflow: hidden; }
    .progress-fill {
        height: 100%;
        border-radius: 3px;
        background: linear-gradient(90deg, #0e7490, #06b6d4);
        transition: width .4s ease;
    }
</style>
@endsection

@section('content')
<div class="space-y-6">

    {{-- ── Encabezado de la persona ── --}}
    <div class="panel p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
            <div class="flex items-center gap-4 min-w-0">
                <div class="avatar-lg" aria-hidden="true">
                    {{ strtoupper(mb_substr($persona->nombres, 0, 1) . mb_substr($persona->apellidos, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-3 mb-1">
                        <h2 class="text-xl sm:text-2xl font-bold font-jakarta text-white truncate">
                            {{ $persona->nombres }} {{ $persona->apellidos }}
                        </h2>
                        @php
                            $badgeClass = match($persona->estado) {
                                'activo'   => 'badge-activo',
                                'lead'     => 'badge-lead',
                                'inactivo' => 'badge-inactivo',
                                default    => 'badge-default'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $persona->estado ?? '—' }}</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-400">
                        <a href="mailto:{{ $persona->email }}" class="hover:text-nm-cyan transition-colors flex items-center gap-1.5">
                            <x-icon name="envelope" class="w-4 h-4 text-slate-500" />
                            {{ $persona->email }}
                        </a>
                        @if($persona->telefono)
                            <span class="flex items-center gap-1.5 text-slate-400">
                                <x-icon name="phone" class="w-4 h-4 text-slate-500" />
                                {{ $persona->telefono }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <a href="{{ route('personas.index') }}"
               class="admin-focus inline-flex items-center gap-2 text-sm text-slate-300 hover:text-white px-3.5 py-2 rounded-lg border border-slate-700/80 hover:border-nm-cyan hover:bg-slate-800/60 transition-colors whitespace-nowrap self-stretch sm:self-auto justify-center">
                <x-icon name="arrow-left" class="w-4 h-4 text-slate-400" />
                Volver al listado
            </a>
        </div>
    </div>

    {{-- ── Columnas de datos ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Datos personales ── --}}
        <div class="panel p-5 lg:col-span-1">
            <h3 class="section-title flex items-center gap-2">
                <x-icon name="identification" class="w-4 h-4 text-nm-cyan" />
                Datos personales
            </h3>
            <div class="space-y-0.5">
                <div class="info-row">
                    <span class="info-label">Documento</span>
                    <span class="info-value">{{ $persona->tipo_documento ?? '—' }} {{ $persona->numero_documento ?? '' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">País doc.</span>
                    <span class="info-value">{{ $persona->pais_documento ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Género</span>
                    <span class="info-value">{{ $persona->genero ? ucfirst($persona->genero) : '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nacimiento</span>
                    <span class="info-value">
                        {{ $persona->fecha_nacimiento ? \Carbon\Carbon::parse($persona->fecha_nacimiento)->format('d/m/Y') : '—' }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">País / Ciudad</span>
                    <span class="info-value">{{ $persona->pais ?? '—' }}@if($persona->ciudad), {{ $persona->ciudad }}@endif</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nivel educacional</span>
                    <span class="info-value">{{ $persona->nivel_educacional ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Origen lead</span>
                    <span class="info-value">{{ $persona->origen_lead ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha registro</span>
                    <span class="info-value">
                        {{ $persona->creado_en ? \Carbon\Carbon::parse($persona->creado_en)->format('d/m/Y H:i') : '—' }}
                    </span>
                </div>
                @if($persona->linkedin)
                <div class="info-row">
                    <span class="info-label">LinkedIn</span>
                    <a href="{{ $persona->linkedin }}" target="_blank" rel="noopener noreferrer"
                       class="info-value text-nm-cyan hover:text-cyan-300 inline-flex items-center gap-1 truncate">
                        <span>Ver perfil</span>
                        <x-icon name="arrow-up-right" class="w-3.5 h-3.5" />
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- ── Historial laboral ── --}}
        <div class="panel p-5 lg:col-span-2">
            <h3 class="section-title flex items-center gap-2">
                <x-icon name="briefcase" class="w-4 h-4 text-nm-cyan" />
                Historial laboral
            </h3>
            @if($persona->historialLaboral->isEmpty())
                <div class="py-12 text-center text-slate-400 text-sm">
                    <x-icon name="briefcase" class="w-8 h-8 mx-auto text-slate-600 mb-2" />
                    Sin historial laboral registrado.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($persona->historialLaboral as $hl)
                    <div class="rounded-xl border border-slate-800/80 p-4 bg-slate-900/60 hover:border-slate-700/80 transition-colors">
                        <div class="flex justify-between items-start flex-wrap gap-2">
                            <div>
                                <p class="font-semibold text-white text-sm">{{ $hl->cargo ?? 'Sin cargo' }}</p>
                                <p class="text-slate-400 text-xs mt-0.5">
                                    {{ $hl->empresa->razon_social ?? 'Empresa no registrada' }}
                                    @if($hl->area) · <span class="text-slate-500">{{ $hl->area }}</span>@endif
                                </p>
                            </div>
                            <div class="text-right text-xs text-slate-400">
                                {{ $hl->fecha_inicio ? \Carbon\Carbon::parse($hl->fecha_inicio)->format('M Y') : '?' }}
                                →
                                {{ $hl->fecha_fin ? \Carbon\Carbon::parse($hl->fecha_fin)->format('M Y') : 'Presente' }}
                            </div>
                        </div>
                        @if($hl->nivel_jerarquico)
                        <span class="inline-block mt-2.5 px-2.5 py-0.5 rounded-full text-xs font-medium
                                     bg-nm-cyan/10 text-nm-cyan border border-nm-cyan/20">
                            {{ $hl->nivel_jerarquico }}
                        </span>
                        @endif
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ── Inscripciones ── --}}
    <div class="panel overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="section-title mb-0 flex items-center gap-2">
                <x-icon name="academic-cap" class="w-4 h-4 text-nm-cyan" />
                Inscripciones
                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full
                             text-xs font-semibold bg-nm-cyan/10 text-nm-cyan border border-nm-cyan/25 normal-case tracking-normal">
                    {{ $persona->inscripciones->count() }}
                </span>
            </h3>
        </div>

        @if($persona->inscripciones->isEmpty())
            <div class="py-14 text-center text-slate-400 text-sm">
                <x-icon name="academic-cap" class="w-8 h-8 mx-auto text-slate-600 mb-2" />
                Sin inscripciones registradas.
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="ins-table" aria-label="Inscripciones de la persona">
                <thead>
                    <tr>
                        <th scope="col">Programa / Cohorte</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Avance</th>
                        <th scope="col" class="hidden sm:table-cell">Asistencia</th>
                        <th scope="col" class="hidden md:table-cell">Nota final</th>
                        <th scope="col" class="hidden lg:table-cell">Inscripción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($persona->inscripciones as $ins)
                    @php
                        $avance     = $ins->avance_pct ?? 0;
                        $asistencia = $ins->asistencia->asistencia_pct ?? null;
                        $esBadge    = match($ins->estado) {
                            'aprobado'              => 'badge-activo',
                            'inscrito','en_progreso' => 'badge-lead',
                            'retirado','reprobado'   => 'badge-inactivo',
                            default                 => 'badge-default',
                        };
                    @endphp
                    <tr>
                        <td>
                            <p class="font-semibold text-white text-sm">
                                {{ $ins->cohorte->programa->nombre ?? 'Sin programa' }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ $ins->cohorte->codigo ?? 'Cohorte #' . $ins->cohorte_id }}
                                @if($ins->cohorte->fecha_inicio)
                                    · {{ \Carbon\Carbon::parse($ins->cohorte->fecha_inicio)->format('M Y') }}
                                @endif
                            </p>
                        </td>
                        <td>
                            <span class="badge {{ $esBadge }}">{{ $ins->estado }}</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-2 min-w-[120px]">
                                <div class="progress-bar flex-1" aria-hidden="true">
                                    <div class="progress-fill" style="width: {{ min(100, max(0, $avance)) }}%"></div>
                                </div>
                                <span class="text-xs text-slate-300 w-9 text-right font-medium">{{ $avance }}%</span>
                            </div>
                        </td>
                        <td class="hidden sm:table-cell text-slate-300">
                            {{ $asistencia !== null ? $asistencia . '%' : '—' }}
                        </td>
                        <td class="hidden md:table-cell text-slate-300 font-medium">
                            {{ $ins->nota_final ?? '—' }}
                        </td>
                        <td class="hidden lg:table-cell text-slate-400 text-xs">
                            {{ $ins->fecha ? \Carbon\Carbon::parse($ins->fecha)->format('d/m/Y') : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection
