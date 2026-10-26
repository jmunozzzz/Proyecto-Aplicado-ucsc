

<section id="propuesta" class="bg-nm-dark2 py-24 lg:py-32 relative overflow-hidden">

    {{-- ── Background decorations ── --}}
    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-nm-cyan/30 to-transparent pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-nm-cyan/20 to-transparent pointer-events-none"></div>
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-nm-cyan/5 rounded-full blur-3xl pointer-events-none blob"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-nm-cyan/5 rounded-full blur-3xl pointer-events-none blob" style="animation-delay:-4s"></div>

    <div class="max-w-7xl mx-auto px-5 lg:px-10">

        {{-- ── Section Header ── --}}
        <div class="text-center max-w-3xl mx-auto mb-16 lg:mb-20 fade-in-up">
            <span class="inline-block bg-nm-cyan/10 border border-nm-cyan/30 text-nm-cyan text-xs font-semibold font-jakarta
                         px-4 py-1.5 rounded-full tracking-wider uppercase mb-5">
                Metodología Comprobada
            </span>
            <h2 class="font-jakarta font-black text-3xl sm:text-4xl lg:text-5xl text-white leading-[1.15] tracking-tight mb-5">
                La Metodología<br>
                <span class="text-gradient">Neuro Management</span>
            </h2>
            <p class="text-slate-400 text-lg leading-relaxed">
                Un sistema de transformación ejecutiva que combina neurociencia aplicada, estrategia organizacional y desarrollo humano para generar cambios reales y sostenibles.
            </p>
        </div>

        {{-- ── 4 Pilares ── --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-20">

            @php
            $pilares = [
                [
                    'num'   => '01',
                    'title' => 'Claridad Estratégica',
                    'desc'  => 'Definimos con precisión quirúrgica hacia dónde va la organización, qué priorizar y cómo tomar decisiones de alto impacto con confianza.',
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>',
                    'color' => 'blue',
                ],
                [
                    'num'   => '02',
                    'title' => 'Liderazgo de Alto Impacto',
                    'desc'  => 'Desarrollamos el perfil neurológico del liderazgo: cómo el cerebro toma decisiones, lidera bajo presión y genera influencia real en el equipo.',
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
                    'color' => 'cyan',
                ],
                [
                    'num'   => '03',
                    'title' => 'Capacidad Organizacional',
                    'desc'  => 'Construimos sistemas y estructuras que permiten a la organización ejecutar con velocidad, adaptarse al cambio y escalar sus resultados.',
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
                    'color' => 'teal',
                ],
                [
                    'num'   => '04',
                    'title' => 'Cultura de Ejecución',
                    'desc'  => 'Transformamos la cultura organizacional en un activo estratégico: equipos comprometidos, con propósito claro y alto rendimiento sostenido.',
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
                    'color' => 'violet',
                ],
            ];
            $colorMap = [
                'blue'   => ['bg' => 'bg-blue-500/10',   'icon' => 'text-blue-400',   'border' => 'hover:border-blue-400/40'],
                'cyan'   => ['bg' => 'bg-nm-cyan/10',    'icon' => 'text-nm-cyan',    'border' => 'hover:border-nm-cyan/40'],
                'teal'   => ['bg' => 'bg-teal-500/10',   'icon' => 'text-teal-400',   'border' => 'hover:border-teal-400/40'],
                'violet' => ['bg' => 'bg-violet-500/10', 'icon' => 'text-violet-400', 'border' => 'hover:border-violet-400/40'],
            ];
            @endphp

            @foreach($pilares as $i => $pilar)
            @php $c = $colorMap[$pilar['color']]; @endphp
            <div class="group gradient-border rounded-2xl p-6 {{ $pilar['color'] === 'cyan' ? 'hover:border-nm-cyan/50' : $c['border'] }}
                        transition-all duration-400 card-lift fade-in-up fade-delay-{{ $i+1 }} cursor-default relative overflow-hidden">

                {{-- Shimmer on hover --}}
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 shimmer pointer-events-none rounded-2xl"></div>

                {{-- Number --}}
                <div class="font-jakarta font-black text-6xl text-white/5 absolute top-4 right-4 select-none leading-none">{{ $pilar['num'] }}</div>

                {{-- Icon --}}
                <div class="w-12 h-12 {{ $c['bg'] }} rounded-xl flex items-center justify-center mb-5 relative z-10 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $pilar['icon'] !!}
                    </svg>
                </div>

                {{-- Label --}}
                <span class="text-[10px] font-bold tracking-widest uppercase {{ $c['icon'] }} opacity-70 mb-3 block">{{ $pilar['num'] }}</span>

                {{-- Title --}}
                <h3 class="font-jakarta font-bold text-white text-lg leading-tight mb-3">{{ $pilar['title'] }}</h3>

                {{-- Desc --}}
                <p class="text-slate-400 text-sm leading-relaxed">{{ $pilar['desc'] }}</p>

                {{-- Bottom line --}}
                <div class="mt-5 w-8 h-0.5 {{ str_replace('text-', 'bg-', $c['icon']) }} rounded-full group-hover:w-full transition-all duration-500"></div>
            </div>
            @endforeach
        </div>

        {{-- ── Process steps row ── --}}
        <div class="fade-in-up">
            <p class="text-center text-slate-500 text-sm uppercase tracking-widest font-semibold mb-8">Nuestro proceso en 3 pasos</p>
            <div class="grid md:grid-cols-3 gap-8 relative">
                {{-- Connecting line --}}
                <div class="hidden md:block absolute top-8 left-1/4 right-1/4 h-px bg-gradient-to-r from-nm-cyan/20 via-nm-cyan/60 to-nm-cyan/20 pointer-events-none"></div>

                @foreach([
                    ['step' => '1', 'title' => 'Diagnóstico', 'desc' => 'Sesión de diagnóstico profundo (90 min) para identificar brechas, oportunidades y el estado actual de la organización.'],
                    ['step' => '2', 'title' => 'Diseño del Plan', 'desc' => 'Co-creamos un plan estratégico personalizado con objetivos claros, KPIs y metodología adaptada a tu contexto.'],
                    ['step' => '3', 'title' => 'Acompañamiento', 'desc' => 'Implementación con seguimiento continuo, ajustes en tiempo real y aseguramiento de resultados concretos y medibles.'],
                ] as $step)
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-nm-cyan/10 border border-nm-cyan/30 rounded-full flex items-center justify-center relative">
                        <span class="font-jakarta font-black text-nm-cyan text-xl">{{ $step['step'] }}</span>
                        <div class="absolute inset-0 rounded-full border border-nm-cyan/20 scale-125"></div>
                    </div>
                    <h4 class="font-jakarta font-bold text-white text-base mb-2">{{ $step['title'] }}</h4>
                    <p class="text-slate-400 text-sm leading-relaxed">{{ $step['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</section>
