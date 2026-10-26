

<section id="resultados" class="bg-nm-dark2 py-24 lg:py-32 relative overflow-hidden">

    {{-- ── Background ── --}}
    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-nm-cyan/30 to-transparent pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-nm-cyan/20 to-transparent pointer-events-none"></div>
    <div class="absolute inset-0 bg-dots opacity-20 pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[400px] bg-nm-cyan/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-5 lg:px-10">

        {{-- ── Header ── --}}
        <div class="text-center max-w-3xl mx-auto mb-16 fade-in-up">
            <span class="inline-block bg-nm-cyan/10 border border-nm-cyan/30 text-nm-cyan text-xs font-semibold font-jakarta
                         px-4 py-1.5 rounded-full tracking-wider uppercase mb-5">
                Resultados Comprobados
            </span>
            <h2 class="font-jakarta font-black text-3xl sm:text-4xl lg:text-5xl text-white leading-[1.15] tracking-tight mb-5">
                Números que<br>
                <span class="text-gradient">hablan por sí solos</span>
            </h2>
            <p class="text-slate-400 text-lg leading-relaxed">
                Más de una década generando impacto real y medible en organizaciones de Latinoamérica.
            </p>
        </div>

        {{-- ── Main Stats Grid ── --}}
        @php
        $stats = [
            ['num' => 200, 'suffix' => '+', 'label' => 'Empresas Transformadas',    'sub' => 'En 8 países de Latinoamérica', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>'],
            ['num' => 95,  'suffix' => '%', 'label' => 'Tasa de Satisfacción',     'sub' => 'Promedio de evaluación post-proceso', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>'],
            ['num' => 12,  'suffix' => '+', 'label' => 'Años de Experiencia',      'sub' => 'Consultoría ejecutiva de alto nivel', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
            ['num' => 38,  'suffix' => '%', 'label' => 'Crecimiento Promedio',     'sub' => 'En resultados financieros de clientes', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>'],
        ];
        @endphp

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
            @foreach($stats as $i => $stat)
            <div class="group text-center gradient-border rounded-2xl p-8 fade-in-up fade-delay-{{ $i+1 }} card-lift cursor-default relative overflow-hidden">
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 shimmer pointer-events-none rounded-2xl"></div>

                {{-- Icon --}}
                <div class="w-12 h-12 mx-auto mb-5 bg-nm-cyan/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-nm-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $stat['icon'] !!}
                    </svg>
                </div>

                {{-- Counter --}}
                <div class="font-jakarta font-black text-5xl lg:text-6xl text-white leading-none mb-2">
                    <span class="stat-num" data-target="{{ $stat['num'] }}" data-suffix="{{ $stat['suffix'] }}">0</span>{{ $stat['suffix'] }}
                </div>

                {{-- Label --}}
                <div class="font-jakarta font-bold text-nm-cyan text-base mb-1">{{ $stat['label'] }}</div>
                <div class="text-slate-500 text-xs leading-relaxed">{{ $stat['sub'] }}</div>
            </div>
            @endforeach
        </div>

        {{-- ── Progress bars section ── --}}
        <div class="grid md:grid-cols-2 gap-12 fade-in-up">
            <div class="space-y-6">
                <h3 class="font-jakarta font-bold text-white text-xl mb-6">Áreas de mayor impacto</h3>
                @foreach([
                    ['label' => 'Claridad y Decisión Estratégica',   'pct' => 94, 'color' => 'bg-nm-cyan'],
                    ['label' => 'Liderazgo y Equipos de Alto Impacto','pct' => 91, 'color' => 'bg-blue-400'],
                    ['label' => 'Cultura Organizacional',             'pct' => 88, 'color' => 'bg-teal-400'],
                    ['label' => 'Resultados Financieros',             'pct' => 86, 'color' => 'bg-violet-400'],
                ] as $bar)
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-slate-300 font-medium">{{ $bar['label'] }}</span>
                        <span class="text-nm-cyan font-bold font-jakarta">{{ $bar['pct'] }}%</span>
                    </div>
                    <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
                        <div class="progress-bar h-full {{ $bar['color'] }} rounded-full" data-width="{{ $bar['pct'] }}"></div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="space-y-5">
                <h3 class="font-jakarta font-bold text-white text-xl mb-6">Industrias atendidas</h3>
                @foreach([
                    ['industria' => 'Retail & Consumo Masivo',    'num' => '60+', 'icono' => '🏪'],
                    ['industria' => 'Tecnología & Startups',      'num' => '45+', 'icono' => '💻'],
                    ['industria' => 'Banca & Servicios Financieros','num' => '38+', 'icono' => '🏦'],
                    ['industria' => 'Manufactura & Industria',    'num' => '32+', 'icono' => '🏭'],
                    ['industria' => 'Salud & Bienestar',          'num' => '25+', 'icono' => '🏥'],
                ] as $ind)
                <div class="flex items-center justify-between py-3 border-b border-slate-700/50">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">{{ $ind['icono'] }}</span>
                        <span class="text-slate-300 text-sm font-medium">{{ $ind['industria'] }}</span>
                    </div>
                    <span class="font-jakarta font-bold text-nm-cyan text-sm">{{ $ind['num'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- ── Counter + Progress Bar Animation JS ── --}}
    <script>
        // Stats counter animation
        function animateCounter(el) {
            const target = parseInt(el.dataset.target);
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;
            const timer = setInterval(() => {
                current = Math.min(current + step, target);
                el.textContent = Math.floor(current);
                if (current >= target) {
                    el.textContent = target;
                    clearInterval(timer);
                }
            }, 16);
        }

        // Progress bars
        function animateBars() {
            document.querySelectorAll('.progress-bar').forEach(bar => {
                bar.style.width = bar.dataset.width + '%';
            });
        }

        // Trigger on scroll
        const statsSection = document.getElementById('resultados');
        let statsAnimated = false;
        const statsObserver = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !statsAnimated) {
                statsAnimated = true;
                document.querySelectorAll('.stat-num').forEach(animateCounter);
                setTimeout(animateBars, 300);
            }
        }, { threshold: 0.3 });
        statsObserver.observe(statsSection);
    </script>
</section>
