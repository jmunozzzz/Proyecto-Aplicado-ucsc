@extends('layouts.app')

@section('title', $persona->nombres . ' ' . $persona->apellidos . ' — Neuro Management')

@section('styles')
<style>
    body { background: #0a1120; color: #e2e8f0; }

    .panel {
        background: rgba(15,23,42,.95);
        border: 1px solid rgba(6,182,212,.15);
        border-radius: 1rem;
    }
    .section-title {
        font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .1em;
        color: #06b6d4; margin-bottom: .75rem;
    }
    .info-row {
        display: flex; align-items: baseline; gap: .5rem;
        padding: .5rem 0; border-bottom: 1px solid rgba(30,41,59,.6);
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { font-size: .75rem; color: #64748b; min-width: 9rem; flex-shrink: 0; }
    .info-value { font-size: .875rem; color: #e2e8f0; }

    .avatar-lg {
        width: 72px; height: 72px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; font-weight: 800;
        background: linear-gradient(135deg, #0e7490, #06b6d4);
        color: white; flex-shrink: 0;
        box-shadow: 0 0 30px rgba(6,182,212,.3);
    }
    .badge {
        display: inline-flex; align-items: center;
        padding: .25rem .75rem; border-radius: 9999px;
        font-size: .7rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: .05em;
    }
    .badge-activo    { background: rgba(16,185,129,.15); color: #34d399; border: 1px solid rgba(52,211,153,.25); }
    .badge-lead      { background: rgba(245,158,11,.12);  color: #fbbf24; border: 1px solid rgba(251,191,36,.2); }
    .badge-inactivo  { background: rgba(100,116,139,.12); color: #94a3b8; border: 1px solid rgba(148,163,184,.2); }
    .badge-default   { background: rgba(6,182,212,.1);    color: #67e8f9; border: 1px solid rgba(103,232,249,.2); }

    /* Inscripciones table */
    .ins-table { width: 100%; border-collapse: collapse; }
    .ins-table th {
        padding: .7rem 1rem; text-align: left;
        font-size: .7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: #64748b;
        border-bottom: 1px solid rgba(6,182,212,.15);
    }
    .ins-table td {
        padding: .75rem 1rem; font-size: .8rem;
        border-bottom: 1px solid rgba(30,41,59,.5);
        vertical-align: middle;
    }
    .ins-table tr:last-child td { border-bottom: none; }
    .ins-table tr:hover td { background: rgba(6,182,212,.03); }

    /* Progress bar */
    .progress-bar { height: 6px; border-radius: 3px; background: rgba(30,41,59,.8); overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 3px;
                     background: linear-gradient(90deg, #0e7490, #06b6d4); transition: width .6s ease; }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-nm-darkest px-4 pt-24 pb-8 lg:px-8">
    <div class="max-w-screen-lg mx-auto space-y-6">

        {{-- ── Breadcrumb ── --}}
        <nav class="flex items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-nm-cyan transition-colors">Inicio</a>
            <span>/</span>
            <a href="{{ route('personas.index') }}" class="hover:text-nm-cyan transition-colors">Personas</a>
            <span>/</span>
            <span class="text-slate-300">{{ $persona->nombres }} {{ $persona->apellidos }}</span>
        </nav>

        {{-- ── Profile header ── --}}
        <div class="panel p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                <div class="avatar-lg">
                    {{ strtoupper(mb_substr($persona->nombres,0,1).mb_substr($persona->apellidos,0,1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-3 mb-1">
                        <h1 class="text-2xl font-bold font-jakarta text-white">
                            {{ $persona->nombres }} {{ $persona->apellidos }}
                        </h1>
                        @php $badgeClass = match($persona->estado) {
                            'activo'=>'badge-activo','lead'=>'badge-lead','inactivo'=>'badge-inactivo',default=>'badge-default'
                        }; @endphp
                        <span class="badge {{ $badgeClass }}">{{ $persona->estado ?? '—' }}</span>
                    </div>
                    <p class="text-slate-400 text-sm">{{ $persona->email }}</p>
                    @if($persona->telefono)
                    <p class="text-slate-500 text-sm">{{ $persona->telefono }}</p>
                    @endif
                </div>
                <a href="{{ route('personas.index') }}"
                   class="text-sm text-slate-400 hover:text-nm-cyan transition-colors whitespace-nowrap">
                    ← Volver al listado
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── Datos personales ── --}}
            <div class="panel p-5 lg:col-span-1">
                <p class="section-title">Datos personales</p>
                <div>
                    <div class="info-row">
                        <span class="info-label">Documento</span>
                        <span class="info-value">{{ $persona->tipo_documento }} {{ $persona->numero_documento ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">País doc.</span>
                        <span class="info-value">{{ $persona->pais_documento ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Género</span>
                        <span class="info-value">{{ $persona->genero ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Nacimiento</span>
                        <span class="info-value">
                            {{ $persona->fecha_nacimiento ? \Carbon\Carbon::parse($persona->fecha_nacimiento)->format('d/m/Y') : '—' }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">País / Ciudad</span>
                        <span class="info-value">{{ $persona->pais ?? '—' }} / {{ $persona->ciudad ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Nivel ed.</span>
                        <span class="info-value">{{ $persona->nivel_educacional ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Origen lead</span>
                        <span class="info-value">{{ $persona->origen_lead ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Registro</span>
                        <span class="info-value">
                            {{ $persona->creado_en ? \Carbon\Carbon::parse($persona->creado_en)->format('d/m/Y H:i') : '—' }}
                        </span>
                    </div>
                    @if($persona->linkedin)
                    <div class="info-row">
                        <span class="info-label">LinkedIn</span>
                        <a href="{{ $persona->linkedin }}" target="_blank" rel="noopener"
                           class="info-value text-nm-cyan hover:underline truncate max-w-[160px] block">
                            Ver perfil →
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── Historial laboral ── --}}
            <div class="panel p-5 lg:col-span-2">
                <p class="section-title">Historial laboral</p>
                @if($persona->historialLaboral->isEmpty())
                    <p class="text-slate-500 text-sm text-center py-8">Sin historial laboral registrado.</p>
                @else
                    <div class="space-y-3">
                        @foreach($persona->historialLaboral as $hl)
                        <div class="rounded-lg border border-slate-800 p-4 bg-slate-900/40">
                            <div class="flex justify-between items-start flex-wrap gap-2">
                                <div>
                                    <p class="font-semibold text-white text-sm">{{ $hl->cargo ?? 'Sin cargo' }}</p>
                                    <p class="text-slate-400 text-xs mt-0.5">
                                        {{ $hl->empresa->razon_social ?? 'Empresa no registrada' }}
                                        @if($hl->area) · <span class="text-slate-500">{{ $hl->area }}</span>@endif
                                    </p>
                                </div>
                                <div class="text-right text-xs text-slate-600">
                                    {{ $hl->fecha_inicio ? \Carbon\Carbon::parse($hl->fecha_inicio)->format('M Y') : '?' }}
                                    →
                                    {{ $hl->fecha_fin ? \Carbon\Carbon::parse($hl->fecha_fin)->format('M Y') : 'Presente' }}
                                </div>
                            </div>
                            @if($hl->nivel_jerarquico)
                            <span class="inline-block mt-2 px-2 py-0.5 rounded-full text-xs
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
                <p class="section-title mb-0">
                    Inscripciones
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full
                                 text-xs font-semibold bg-nm-cyan/10 text-nm-cyan border border-nm-cyan/20 normal-case tracking-normal">
                        {{ $persona->inscripciones->count() }}
                    </span>
                </p>
            </div>
            @if($persona->inscripciones->isEmpty())
                <p class="text-slate-500 text-sm text-center py-12">Sin inscripciones registradas.</p>
            @else
            <div class="overflow-x-auto">
                <table class="ins-table">
                    <thead>
                        <tr>
                            <th>Programa / Cohorte</th>
                            <th>Estado</th>
                            <th>Avance</th>
                            <th class="hidden sm:table-cell">Asistencia</th>
                            <th class="hidden md:table-cell">Nota final</th>
                            <th class="hidden lg:table-cell">Inscripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($persona->inscripciones as $ins)
                        @php
                            $avance     = $ins->avance_pct ?? 0;
                            $asistencia = $ins->asistencia->asistencia_pct ?? null;
                            $esBadge    = match($ins->estado) {
                                'aprobado'  => 'badge-activo',
                                'inscrito','en_progreso' => 'badge-lead',
                                'retirado','reprobado'   => 'badge-inactivo',
                                default => 'badge-default',
                            };
                        @endphp
                        <tr>
                            <td>
                                <p class="font-semibold text-white text-sm">
                                    {{ $ins->cohorte->programa->nombre ?? 'Sin programa' }}
                                </p>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    {{ $ins->cohorte->codigo ?? 'Cohorte #'.$ins->cohorte_id }}
                                    @if($ins->cohorte->fecha_inicio)
                                        · {{ \Carbon\Carbon::parse($ins->cohorte->fecha_inicio)->format('M Y') }}
                                    @endif
                                </p>
                            </td>
                            <td>
                                <span class="badge {{ $esBadge }}">{{ $ins->estado }}</span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2 min-w-[100px]">
                                    <div class="progress-bar flex-1">
                                        <div class="progress-fill" style="width:{{ $avance }}%"></div>
                                    </div>
                                    <span class="text-xs text-slate-400 w-8 text-right">{{ $avance }}%</span>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell text-slate-300">
                                {{ $asistencia !== null ? $asistencia.'%' : '—' }}
                            </td>
                            <td class="hidden md:table-cell text-slate-300">
                                {{ $ins->nota_final ?? '—' }}
                            </td>
                            <td class="hidden lg:table-cell text-slate-500 text-xs">
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
</div>
@endsection
