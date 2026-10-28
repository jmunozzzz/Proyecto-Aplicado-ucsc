

<section id="hero" class="relative min-h-screen bg-[#0a1120] flex flex-col justify-center overflow-hidden">

    {{-- ── Decorative blobs ── --}}
    <div class="absolute top-1/4 -left-40 w-96 h-96 bg-nm-cyan/10 rounded-full blur-3xl blob pointer-events-none"></div>
    <div class="absolute bottom-1/4 -right-40 w-80 h-80 bg-nm-cyan2/10 rounded-full blur-3xl blob pointer-events-none" style="animation-delay: -3s;"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-nm-cyan/5 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-5 lg:px-10 pt-32 pb-20 grid lg:grid-cols-2 gap-12 lg:gap-16 items-center w-full">

        {{-- ── LEFT: Copy ── --}}
        <div class="space-y-8">

            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 bg-nm-cyan/10 border border-nm-cyan/30 text-nm-cyan
                        text-xs font-semibold font-jakarta px-4 py-2 rounded-full tracking-wider uppercase fade-in-up">
                <span class="w-2 h-2 bg-nm-cyan rounded-full animate-pulse"></span>
                Consultoría Ejecutiva de Alto Nivel
            </div>

            {{-- H1 --}}
            <h1 class="font-jakarta font-black text-4xl sm:text-5xl xl:text-6xl leading-[1.1] tracking-tight text-white fade-in-up fade-delay-1">
                Transforma tu<br>
                <span class="text-gradient">Liderazgo.</span><br>
                Transforma tu <span class="text-gradient">Empresa.</span>
            </h1>

            {{-- Subtitle --}}
            <p class="text-slate-400 text-lg leading-relaxed max-w-lg fade-in-up fade-delay-2">
                Trabajamos con gerentes, dueños de empresa y alta dirección para destrabar el verdadero potencial de sus organizaciones a través de la
                <strong class="text-slate-200 font-semibold">neurociencia aplicada al management</strong>.
            </p>

            {{-- CTAs --}}
            <div class="flex flex-col sm:flex-row gap-4 fade-in-up fade-delay-3">
                <a href="#contacto"
                   class="group inline-flex items-center justify-center gap-2 bg-nm-cyan hover:bg-cyan-400 text-white
                          font-semibold font-jakarta text-base px-7 py-4 rounded-xl transition-all duration-300
                          hover:shadow-xl hover:shadow-cyan-500/30 hover:-translate-y-1">
                    Agendar Reunión Estratégica
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
                <a href="#jorge"
                   class="inline-flex items-center justify-center gap-2 border border-slate-600 hover:border-nm-cyan
                          text-slate-300 hover:text-nm-cyan font-medium text-base px-7 py-4 rounded-xl transition-all duration-300 hover:bg-nm-cyan/5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Ver mi trabajo
                </a>
            </div>

            {{-- Trust badges --}}
            <div class="fade-in-up fade-delay-4">
                <p class="text-slate-600 text-xs uppercase tracking-widest font-medium mb-4">Con quienes hemos trabajado</p>
                <div class="flex flex-wrap gap-6 items-center">
                    @foreach(['Retail Corp', 'Banca & Finanzas', 'Industrial S.A.', 'Tech Ventures', 'Grupo Holdings'] as $empresa)
                    <span class="text-slate-500 text-sm font-semibold tracking-tight hover:text-slate-300 transition-colors cursor-default">{{ $empresa }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── RIGHT: Executive Image ── --}}
        <div class="relative flex justify-center lg:justify-end fade-in-up fade-delay-2">

            {{-- Background glow --}}
            <div class="absolute inset-0 bg-nm-cyan/15 rounded-3xl blur-3xl scale-75 pointer-events-none"></div>

            {{-- Decorative ring --}}
            <div class="relative">
                <div class="absolute -inset-4 border border-nm-cyan/20 rounded-3xl"></div>
                <div class="absolute -inset-8 border border-nm-cyan/10 rounded-3xl"></div>

                {{-- Main image placeholder --}}
                <div class="relative w-72 sm:w-80 lg:w-96 h-[440px] lg:h-[520px] rounded-3xl overflow-hidden
                            gradient-border shadow-2xl floating">
                    {{-- Overlay gradient --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-nm-dark via-transparent to-transparent opacity-60"></div>
                    {{-- Name card --}}
                    <div class="absolute bottom-5 left-5 right-5">
                        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-4">
                            <div class="font-jakarta font-bold text-white text-lg">Jorge Vargas</div>
                            <div class="text-nm-cyan text-sm font-medium">Fundador · Neuro Management</div>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                                <span class="text-slate-300 text-xs">Agenda abierta este mes</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Floating stat badge —top right --}}
                <div class="absolute -top-5 -right-5 bg-nm-dark2 border border-nm-cyan/30 rounded-2xl p-3 floating-slow shadow-xl">
                    <div class="font-jakarta font-black text-nm-cyan text-2xl leading-none">+200</div>
                    <div class="text-slate-400 text-xs mt-0.5">Empresas transformadas</div>
                </div>

                {{-- Floating badge — bottom left --}}
                <div class="absolute -bottom-5 -left-5 bg-nm-dark2 border border-nm-cyan/30 rounded-2xl p-3 floating shadow-xl" style="animation-delay:-2s">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-nm-cyan/20 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-nm-cyan" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-jakarta font-bold text-white text-sm">4.9/5</div>
                            <div class="text-slate-400 text-[10px]">Satisfacción clientes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Scroll indicator ── --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-slate-500 opacity-60 hover:opacity-100 transition-opacity">
        <span class="text-xs tracking-widest uppercase font-medium">Explorar</span>
        <div class="w-px h-12 bg-gradient-to-b from-nm-cyan to-transparent floating"></div>
    </div>

    {{-- ── Bottom diagonal clip ── --}}
    <div class="absolute bottom-0 left-0 right-0 h-16 bg-slate-50 pointer-events-none"
         style="clip-path: polygon(0 100%, 100% 100%, 100% 30%, 0 100%); opacity: .6;"></div>

</section>
