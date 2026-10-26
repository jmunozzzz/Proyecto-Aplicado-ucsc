

<section id="problema" class="bg-slate-50 py-24 lg:py-32 relative overflow-hidden">

    {{-- ── Subtle background accent ── --}}
    <div class="absolute top-0 right-0 w-96 h-96 bg-nm-cyan/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-5 lg:px-10">

        {{-- ── Section header ── --}}
        <div class="text-center max-w-3xl mx-auto mb-16 fade-in-up">
            <span class="inline-block bg-red-50 border border-red-200/80 text-red-500 text-xs font-semibold font-jakarta
                         px-4 py-1.5 rounded-full tracking-wider uppercase mb-5">
                La Realidad que Nadie Dice
            </span>
            <h2 class="font-jakarta font-black text-3xl sm:text-4xl lg:text-5xl text-nm-dark leading-[1.15] tracking-tight mb-5">
                ¿Te reconoces en alguna<br>
                <span class="text-gradient-cyan">de estas situaciones?</span>
            </h2>
            <p class="text-slate-500 text-lg leading-relaxed">
                Los líderes más capaces enfrentan obstáculos que no aparecen en los libros de gestión.
                Son los que más duelen porque van contra la identidad del líder.
            </p>
        </div>

        {{-- ── Pain points grid ── --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-16">

            @php
            $problemas = [
                [
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    'title' => 'Equipo que no ejecuta',
                    'desc'  => 'Tienes claridad de hacia dónde ir, pero tu equipo no logra acompañar la velocidad ni la dirección que exige el momento.',
                    'tag'   => 'Liderazgo',
                ],
                [
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    'title' => 'Decisiones sin claridad',
                    'desc'  => 'Las reuniones se multiplican, los datos sobran, pero la claridad estratégica para tomar decisiones de alto impacto escasea.',
                    'tag'   => 'Estrategia',
                ],
                [
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
                    'title' => 'Cultura organizacional tóxica',
                    'desc'  => 'El talento rota, la motivación cae y la cultura se convierte en un freno invisible que consume energía y resultados.',
                    'tag'   => 'Cultura',
                ],
                [
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
                    'title' => 'Crecimiento estancado',
                    'desc'  => 'Trabajas más horas que nunca, pero los resultados no escalan. El esfuerzo no se traduce en crecimiento sostenible.',
                    'tag'   => 'Resultados',
                ],
                [
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
                    'title' => 'Liderazgo sin impacto real',
                    'desc'  => 'Sientes que lideras "en soledad". Tus decisiones no calan en la organización y el cambio que impulsas se diluye a mitad de camino.',
                    'tag'   => 'Impacto',
                ],
                [
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                    'title' => 'Visión sin estructura',
                    'desc'  => 'La visión existe en tu cabeza, pero no hay un sistema organizacional que la convierta en resultados medibles y sostenibles.',
                    'tag'   => 'Estructura',
                ],
            ];
            @endphp

            @foreach($problemas as $i => $p)
            <div class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl border border-slate-100 hover:border-nm-cyan/30
                        transition-all duration-400 card-lift fade-in-up fade-delay-{{ min($i+1, 5) }} cursor-default">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 bg-red-50 group-hover:bg-nm-cyan/10 rounded-xl flex items-center justify-center transition-colors duration-300">
                        <svg class="w-6 h-6 text-red-400 group-hover:text-nm-cyan transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $p['icon'] !!}
                        </svg>
                    </div>
                    <span class="text-[10px] font-semibold tracking-widest uppercase text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full">{{ $p['tag'] }}</span>
                </div>
                <h3 class="font-jakarta font-bold text-nm-dark text-lg mb-2 group-hover:text-nm-cyan2 transition-colors">{{ $p['title'] }}</h3>
                <p class="text-slate-500 text-sm leading-relaxed">{{ $p['desc'] }}</p>
                <div class="mt-4 w-0 group-hover:w-full h-0.5 bg-gradient-to-r from-nm-cyan to-nm-cyan2 transition-all duration-500 rounded-full"></div>
            </div>
            @endforeach
        </div>

        {{-- ── CTA Banner ── --}}
        <div class="fade-in-up">
            <div class="bg-gradient-to-r from-nm-dark via-nm-dark2 to-nm-dark rounded-3xl p-8 lg:p-12 flex flex-col lg:flex-row items-center justify-between gap-8 relative overflow-hidden">
                <div class="absolute inset-0 bg-dots opacity-30 pointer-events-none"></div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-nm-cyan/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="text-center lg:text-left relative z-10">
                    <h3 class="font-jakarta font-black text-white text-2xl lg:text-3xl mb-2">
                        Si identificaste alguno de estos patrones, <span class="text-nm-cyan">podemos ayudarte.</span>
                    </h3>
                    <p class="text-slate-400 text-base">No necesitas más información. Necesitas el proceso correcto.</p>
                </div>
                <a href="#contacto"
                   class="relative z-10 flex-shrink-0 inline-flex items-center gap-2 bg-nm-cyan hover:bg-cyan-400 text-white
                          font-semibold font-jakarta px-8 py-4 rounded-xl transition-all duration-300 hover:shadow-xl hover:shadow-cyan-500/30 hover:-translate-y-1">
                    Hablemos ahora
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
        </div>

    </div>
</section>
