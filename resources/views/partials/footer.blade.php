

<footer class="bg-nm-darkest text-white relative overflow-hidden">

    {{-- ── Top border ── --}}
    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-nm-cyan/40 to-transparent pointer-events-none"></div>

    {{-- ── Background decoration ── --}}
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-nm-cyan/3 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 left-0 w-64 h-64 bg-nm-cyan/3 rounded-full blur-3xl pointer-events-none"></div>

    {{-- ── CTA Pre-footer strip ── --}}
    <div class="border-b border-slate-800/60">
        <div class="max-w-7xl mx-auto px-5 lg:px-10 py-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h3 class="font-jakarta font-black text-white text-xl sm:text-2xl">¿Listo para transformar tu organización?</h3>
                <p class="text-slate-400 text-sm mt-1">Agenda tu reunión estratégica gratuita hoy mismo.</p>
            </div>
            <a href="#contacto"
               class="flex-shrink-0 inline-flex items-center gap-2 bg-nm-cyan hover:bg-cyan-400 text-white font-semibold font-jakarta
                      px-7 py-3.5 rounded-xl transition-all duration-300 hover:shadow-lg hover:shadow-cyan-500/30 hover:-translate-y-0.5 whitespace-nowrap">
                Agendar Reunión →
            </a>
        </div>
    </div>

    {{-- ── Main footer content ── --}}
    <div class="max-w-7xl mx-auto px-5 lg:px-10 py-14 grid sm:grid-cols-2 lg:grid-cols-4 gap-10">

        {{-- Col 1: Brand --}}
        <div class="sm:col-span-2 lg:col-span-1">
            <a href="{{ route('home') }}" class="flex items-center gap-3 mb-5 group">
                <div class="w-10 h-10 bg-nm-cyan rounded-xl flex items-center justify-center font-bold text-white text-sm font-jakarta
                            group-hover:bg-cyan-400 transition-colors glow-cyan-sm">
                    NM
                </div>
                <div>
                    <span class="block font-jakarta font-bold text-white text-base leading-none">Neuro Management</span>
                    <span class="block text-nm-cyan text-[11px] font-medium tracking-wider">Jorge Vargas</span>
                </div>
            </a>
            <p class="text-slate-400 text-sm leading-relaxed mb-6">
                Consultoría Ejecutiva de Alto Nivel que transforma organizaciones a través de la neurociencia aplicada al management estratégico.
            </p>
            {{-- Social icons --}}
            <div class="flex gap-3">
                @foreach([
                    ['href' => 'https://youtube.com/@neuromanagement', 'label' => 'YouTube', 'icon' => '<path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>', 'hover' => 'hover:bg-red-500/20 hover:border-red-500/40 hover:text-red-400'],
                    ['href' => 'https://instagram.com/jorgevargas.nm', 'label' => 'Instagram', 'icon' => '<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>', 'hover' => 'hover:bg-pink-500/20 hover:border-pink-500/40 hover:text-pink-400'],
                    ['href' => 'https://linkedin.com/in/jorgevargas-nm', 'label' => 'LinkedIn', 'icon' => '<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>', 'hover' => 'hover:bg-blue-500/20 hover:border-blue-500/40 hover:text-blue-400'],
                ] as $social)
                <a href="{{ $social['href'] }}" target="_blank" rel="noopener noreferrer"
                   aria-label="{{ $social['label'] }}"
                   class="w-9 h-9 border border-slate-700 rounded-lg flex items-center justify-center text-slate-500 transition-all duration-300 {{ $social['hover'] }}">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        {!! $social['icon'] !!}
                    </svg>
                </a>
                @endforeach
            </div>
        </div>

        {{-- Col 2: Servicios --}}
        <div>
            <h4 class="font-jakarta font-bold text-white text-sm uppercase tracking-widest mb-5">Servicios</h4>
            <ul class="space-y-3">
                @foreach([
                    'Diagnóstico Ejecutivo', 'Consultoría Estratégica', 'Desarrollo de Liderazgo',
                    'Cultura Organizacional', 'Conferencias & Keynotes', 'Mentoring Ejecutivo',
                ] as $service)
                <li>
                    <a href="#contacto" class="text-slate-400 hover:text-nm-cyan text-sm transition-colors duration-200 flex items-center gap-2 group">
                        <span class="w-1 h-1 rounded-full bg-slate-700 group-hover:bg-nm-cyan transition-colors flex-shrink-0"></span>
                        {{ $service }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Col 3: Navegación --}}
        <div>
            <h4 class="font-jakarta font-bold text-white text-sm uppercase tracking-widest mb-5">Navegación</h4>
            <ul class="space-y-3">
                @foreach([
                    ['label' => 'Inicio',           'href' => '#'],
                    ['label' => 'Propuesta de Valor','href' => '#propuesta'],
                    ['label' => 'Sobre Jorge',       'href' => '#jorge'],
                    ['label' => 'Casos de Éxito',    'href' => '#testimonios'],
                    ['label' => 'Resultados',         'href' => '#resultados'],
                    ['label' => 'Contacto',           'href' => '#contacto'],
                ] as $link)
                <li>
                    <a href="{{ $link['href'] }}" class="text-slate-400 hover:text-nm-cyan text-sm transition-colors duration-200 flex items-center gap-2 group">
                        <span class="w-1 h-1 rounded-full bg-slate-700 group-hover:bg-nm-cyan transition-colors flex-shrink-0"></span>
                        {{ $link['label'] }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Col 4: Recursos --}}
        <div>
            <h4 class="font-jakarta font-bold text-white text-sm uppercase tracking-widest mb-5">Recursos</h4>
            <ul class="space-y-3">
                @foreach([
                    'Blog Ejecutivo', 'Podcast Neuro Management', 'Newsletter Semanal',
                    'Guía de Liderazgo (PDF)', 'Webinars Gratuitos', 'FAQ',
                ] as $resource)
                <li>
                    <a href="#" class="text-slate-400 hover:text-nm-cyan text-sm transition-colors duration-200 flex items-center gap-2 group">
                        <span class="w-1 h-1 rounded-full bg-slate-700 group-hover:bg-nm-cyan transition-colors flex-shrink-0"></span>
                        {{ $resource }}
                    </a>
                </li>
                @endforeach
            </ul>

            {{-- Newsletter mini --}}
            <div class="mt-7">
                <p class="text-slate-500 text-xs mb-3">Recibe insights ejecutivos semanales:</p>
                <div class="flex gap-2">
                    <input type="email" placeholder="tu@email.com"
                           class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-xs placeholder:text-slate-600
                                  focus:outline-none focus:border-nm-cyan transition-colors">
                    <button class="bg-nm-cyan hover:bg-cyan-400 text-white text-xs font-semibold px-3 py-2 rounded-lg transition-colors flex-shrink-0">
                        →
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Bottom bar ── --}}
    <div class="border-t border-slate-800/60">
        <div class="max-w-7xl mx-auto px-5 lg:px-10 py-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-slate-600 text-xs">
            <span>© {{ date('Y') }} Neuro Management · Jorge Vargas. Todos los derechos reservados.</span>
            <div class="flex gap-5">
                <a href="#" class="hover:text-slate-400 transition-colors">Privacidad</a>
                <a href="#" class="hover:text-slate-400 transition-colors">Términos</a>
                <a href="#" class="hover:text-slate-400 transition-colors">Cookies</a>
            </div>
        </div>
    </div>

    {{-- ── Back to top ── --}}
    <button onclick="window.scrollTo({top:0,behavior:'smooth'})"
            class="fixed bottom-6 right-6 z-40 w-11 h-11 bg-nm-cyan hover:bg-cyan-400 text-white rounded-xl shadow-lg shadow-cyan-500/30
                   flex items-center justify-center transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-cyan-500/40"
            id="back-to-top" aria-label="Volver arriba" style="display:none">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </button>

    <script>
        // Back to top button
        const backTop = document.getElementById('back-to-top');
        window.addEventListener('scroll', () => {
            backTop.style.display = window.scrollY > 400 ? 'flex' : 'none';
        }, { passive: true });
    </script>
</footer>
