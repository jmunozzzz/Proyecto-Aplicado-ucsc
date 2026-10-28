@extends('layouts.admin')

@section('title', 'Inicio')
@section('page_title', 'Inicio')

@section('breadcrumbs')
    <x-admin.breadcrumbs :items="[['label' => 'Inicio']]" />
@endsection

@php
    $disponibles = collect(\App\Support\AdminNavigation::available())->reject(fn ($i) => $i['route'] === 'dashboard');
    $proximos    = \App\Support\AdminNavigation::upcoming();
@endphp

@section('content')
<div class="space-y-8">

    {{-- Bienvenida --}}
    <section class="panel relative overflow-hidden p-6 sm:p-8">
        <div>
            <p class="text-nm-cyan text-xs font-semibold uppercase tracking-widest mb-2">Panel de administración</p>
            <h2 class="font-jakarta text-2xl sm:text-3xl font-bold text-white">
                Hola, {{ auth()->user()->name }}
            </h2>
            <p class="mt-2 text-slate-300 max-w-2xl">
                Bienvenido al sistema administrativo de NeuroManagement Chile. Accede a los módulos disponibles desde aquí o desde el menú lateral.
            </p>
        </div>
    </section>

    {{-- Módulos disponibles --}}
    <section aria-labelledby="modulos-disponibles">
        <h2 id="modulos-disponibles" class="font-jakarta text-lg font-semibold text-white mb-4">Módulos disponibles</h2>

        @if ($disponibles->isEmpty())
            <p class="text-slate-400 text-sm">Aún no hay módulos disponibles.</p>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($disponibles as $mod)
                    <a href="{{ $mod['url'] }}" id="dash-mod-{{ \Illuminate\Support\Str::slug($mod['label']) }}"
                       class="panel group flex items-start gap-4 p-5 hover:border-nm-cyan/50 hover:bg-[#111c33] transition-colors">
                        <span class="w-11 h-11 shrink-0 rounded-xl bg-nm-cyan/10 border border-nm-cyan/25 flex items-center justify-center text-nm-cyan">
                            <x-icon :name="$mod['icon']" class="w-6 h-6" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center gap-2 font-semibold text-white">
                                {{ $mod['label'] }}
                                <x-icon name="arrow-right" class="w-4 h-4 text-slate-500 group-hover:text-nm-cyan group-hover:translate-x-0.5 transition" />
                            </span>
                            @if ($mod['description'])
                                <span class="block mt-1 text-sm text-slate-400">{{ $mod['description'] }}</span>
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Próximamente --}}
    @if (count($proximos))
        <section aria-labelledby="modulos-proximos">
            <h2 id="modulos-proximos" class="font-jakarta text-lg font-semibold text-white mb-1">Próximamente</h2>
            <p class="text-sm text-slate-400 mb-4">Estos módulos aún no están implementados.</p>
            <ul class="flex flex-wrap gap-2">
                @foreach ($proximos as $mod)
                    <li class="inline-flex items-center gap-2 rounded-lg border border-slate-800 bg-slate-900/50 px-3 py-2 text-sm text-slate-400">
                        <x-icon :name="$mod['icon']" class="w-4 h-4 text-slate-500" />
                        {{ $mod['label'] }}
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
@endsection
