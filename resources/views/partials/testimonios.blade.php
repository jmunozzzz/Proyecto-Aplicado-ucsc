

<section id="testimonios" class="bg-nm-dark py-24 lg:py-32 relative overflow-hidden">

    {{-- ── Decorative elements ── --}}
    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-nm-cyan/30 to-transparent pointer-events-none"></div>
    <div class="absolute top-1/2 -left-40 w-96 h-96 bg-nm-cyan/5 rounded-full blur-3xl pointer-events-none blob"></div>
    <div class="absolute top-1/2 -right-40 w-96 h-96 bg-nm-cyan/5 rounded-full blur-3xl pointer-events-none blob" style="animation-delay:-4s"></div>

    {{-- ── Giant quotes decoration ── --}}
    <div class="absolute top-12 left-10 text-nm-cyan/5 font-jakarta font-black text-[200px] leading-none select-none pointer-events-none">"</div>

    <div class="max-w-7xl mx-auto px-5 lg:px-10">

        {{-- ── Header ── --}}
        <div class="text-center max-w-3xl mx-auto mb-14 fade-in-up">
            <span class="inline-block bg-nm-cyan/10 border border-nm-cyan/30 text-nm-cyan text-xs font-semibold font-jakarta
                         px-4 py-1.5 rounded-full tracking-wider uppercase mb-5">
                Casos de Éxito
            </span>
            <h2 class="font-jakarta font-black text-3xl sm:text-4xl lg:text-5xl text-white leading-[1.15] tracking-tight mb-5">
                Lo que dicen quienes<br>
                <span class="text-gradient">ya transformaron su empresa</span>
            </h2>
            <p class="text-slate-400 text-lg leading-relaxed">
                Más de --- organizaciones han transformado sus resultados con la Metodología Neuro Management.
            </p>
        </div>

        {{-- ── Testimonials Slider ── --}}
        <div class="relative fade-in-up fade-delay-1">

            {{-- Slider wrapper --}}
            <div class="overflow-hidden rounded-2xl">
                <div id="slider-track" class="slider-track">
                    @for($i = 0; $i < 3; $i++)
                    <div class="slider-slide px-2">
                        <div class="gradient-border rounded-2xl p-6 sm:p-8 relative overflow-hidden">
                            {{-- Badge de resultado skeleton --}}
                            <div class="absolute top-4 right-4 bg-nm-cyan/10 border border-nm-cyan/20 rounded-xl px-3 py-1.5 flex items-center">
                                <div class="h-3.5 w-20 bg-nm-cyan/30 rounded"></div>
                            </div>

                            {{-- Stars --}}
                            <div class="flex gap-1 mb-5">
                                @for($s = 0; $s < 5; $s++)
                                <svg class="w-4 h-4 text-amber-400/80" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                @endfor
                            </div>

                            {{-- Testimonio (Estructura de líneas de texto) --}}
                            <div class="space-y-3 mb-6">
                                <div class="h-4.5 w-11/12 bg-white/10 rounded"></div>
                                <div class="h-4.5 w-full bg-white/10 rounded"></div>
                                <div class="h-4 w-3/4 bg-white/10 rounded"></div>
                            </div>

                            {{-- Autor (Avatar skeleton + Nombre/Cargo skeleton) --}}
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-white/10 ring-2 ring-nm-cyan/30 flex-shrink-0 flex items-center justify-center text-slate-400">
                                    <svg class="w-6 h-6 text-slate-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                    </svg>
                                </div>
                                <div class="space-y-1.5">
                                    <div class="h-4 w-32 bg-white/10 rounded"></div>
                                    <div class="h-3 w-40 bg-nm-cyan/20 rounded"></div>
                                    <div class="h-3 w-28 bg-white/5 rounded"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>

            {{-- Navigation --}}
            <div class="flex items-center justify-center gap-4 mt-8">
                <button id="prev-btn" onclick="slideTestimonio(-1)"
                        class="w-10 h-10 rounded-full border border-slate-700 hover:border-nm-cyan/50 text-slate-400 hover:text-nm-cyan
                               flex items-center justify-center transition-all duration-300 hover:bg-nm-cyan/10"
                        aria-label="Testimonio anterior">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                {{-- Dots --}}
                <div class="flex gap-2" id="slider-dots">
                    @for($i = 0; $i < 3; $i++)
                    <button onclick="goToSlide({{ $i }})"
                            class="slider-dot w-2 h-2 rounded-full transition-all duration-300 {{ $i === 0 ? 'bg-nm-cyan w-6' : 'bg-slate-600 hover:bg-slate-400' }}"
                            aria-label="Ir a diapositiva {{ $i + 1 }}">
                    </button>
                    @endfor
                </div>

                <button id="next-btn" onclick="slideTestimonio(1)"
                        class="w-10 h-10 rounded-full border border-slate-700 hover:border-nm-cyan/50 text-slate-400 hover:text-nm-cyan
                               flex items-center justify-center transition-all duration-300 hover:bg-nm-cyan/10"
                        aria-label="Siguiente testimonio">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ── Trust logos row ── --}}
        <div class="mt-16 pt-16 border-t border-slate-800 fade-in-up">
            <p class="text-center text-slate-500 text-xs uppercase tracking-widest font-medium mb-8">Empresas que confían en Neuro Management</p>
            <div class="flex flex-wrap justify-center gap-6 lg:gap-8 items-center">
                @for($l = 1; $l <= 6; $l++)
                <div class="h-10 w-32 rounded-xl bg-white/[0.03] border border-white/10 flex items-center justify-center">
                    <div class="h-3 w-16 bg-white/10 rounded"></div>
                </div>
                @endfor
            </div>
        </div>
    </div>

    {{-- ── Testimonials Slider JS ── --}}
    <script>
        let currentSlide = 0;
        const totalSlides = 3;

        function goToSlide(n) {
            currentSlide = ((n % totalSlides) + totalSlides) % totalSlides;
            document.getElementById('slider-track').style.transform = `translateX(-${currentSlide * 100}%)`;
            document.querySelectorAll('.slider-dot').forEach((dot, i) => {
                dot.className = 'slider-dot rounded-full transition-all duration-300 ' +
                    (i === currentSlide ? 'w-6 h-2 bg-nm-cyan' : 'w-2 h-2 bg-slate-600 hover:bg-slate-400');
            });
        }

        function slideTestimonio(dir) { goToSlide(currentSlide + dir); }

        // Auto-slide every 6s
        let autoSlide = setInterval(() => slideTestimonio(1), 6000);
        document.getElementById('slider-track').addEventListener('mouseenter', () => clearInterval(autoSlide));
        document.getElementById('slider-track').addEventListener('mouseleave', () => { autoSlide = setInterval(() => slideTestimonio(1), 6000); });
    </script>
</section>
