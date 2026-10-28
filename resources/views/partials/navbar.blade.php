

<header id="navbar" class="fixed top-0 left-0 right-0 z-50 px-5 lg:px-10 py-4">
    <div class="max-w-7xl mx-auto flex items-center justify-between">

        {{-- ── Logo ── --}}
        <a href="{{ route('home') }}" class="flex items-center gap-3 group" aria-label="Neuro Management - Inicio">
            <div class="w-10 h-10 bg-nm-cyan rounded-xl flex items-center justify-center font-bold text-white text-sm font-jakarta
                        group-hover:bg-cyan-400 transition-all duration-300 shadow-lg shadow-cyan-500/30 glow-cyan-sm">
                NM
            </div>
            <div class="hidden sm:block leading-tight">
                <span class="block text-white font-jakarta font-bold text-base tracking-tight">Neuro Management</span>
                <span class="block text-nm-cyan text-[11px] font-medium tracking-widest uppercase">Jorge Vargas</span>
            </div>
        </a>

        {{-- ── Desktop Navigation ── --}}
        <nav class="hidden lg:flex items-center gap-7" aria-label="Navegación principal">
            <a href="#propuesta"   class="text-slate-300 hover:text-nm-cyan text-sm font-medium transition-colors duration-200">Propuesta</a>
            <a href="#jorge"       class="text-slate-300 hover:text-nm-cyan text-sm font-medium transition-colors duration-200">Sobre Jorge</a>
            <a href="#testimonios" class="text-slate-300 hover:text-nm-cyan text-sm font-medium transition-colors duration-200">Casos de Éxito</a>
            <a href="#resultados"  class="text-slate-300 hover:text-nm-cyan text-sm font-medium transition-colors duration-200">Resultados</a>
            <a href="#contacto"    class="text-slate-300 hover:text-nm-cyan text-sm font-medium transition-colors duration-200">Contacto</a>
        </nav>

        {{-- ── CTA + Mobile Toggle ── --}}
        <div class="flex items-center gap-3">
            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}"
               id="nav-login-btn"
               class="hidden sm:inline-flex items-center gap-2 text-slate-200 hover:text-white text-sm font-semibold font-jakarta
                      px-4 py-2.5 rounded-lg border border-slate-600/70 hover:border-nm-cyan hover:bg-nm-cyan/10
                      transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400">
                <svg class="w-4 h-4 text-nm-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                @auth Panel @else Iniciar sesión @endauth
            </a>

            <a href="#contacto"
               class="hidden sm:inline-flex items-center gap-2 bg-nm-cyan hover:bg-cyan-400 text-white text-sm font-semibold font-jakarta
                      px-5 py-2.5 rounded-lg transition-all duration-300 hover:shadow-lg hover:shadow-cyan-500/30 hover:-translate-y-0.5">
                Agendar Reunión
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>

            {{-- Mobile hamburger --}}
            <button id="mobile-menu-btn"
                    class="lg:hidden text-white p-2 rounded-lg hover:bg-white/10 transition-colors"
                    aria-label="Abrir menú">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- ── Mobile Menu ── --}}
    <div id="mobile-menu"
         class="hidden lg:hidden mt-4 mx-0 bg-nm-dark/98 backdrop-blur-xl rounded-2xl border border-slate-700/50 shadow-2xl overflow-hidden">
        <nav class="flex flex-col divide-y divide-slate-800" aria-label="Menú móvil">
            <a href="#propuesta"   class="px-6 py-4 text-slate-300 hover:text-nm-cyan hover:bg-slate-800/60 text-sm font-medium transition-colors">Propuesta de Valor</a>
            <a href="#jorge"       class="px-6 py-4 text-slate-300 hover:text-nm-cyan hover:bg-slate-800/60 text-sm font-medium transition-colors">Sobre Jorge</a>
            <a href="#testimonios" class="px-6 py-4 text-slate-300 hover:text-nm-cyan hover:bg-slate-800/60 text-sm font-medium transition-colors">Casos de Éxito</a>
            <a href="#resultados"  class="px-6 py-4 text-slate-300 hover:text-nm-cyan hover:bg-slate-800/60 text-sm font-medium transition-colors">Resultados</a>
            <a href="#contacto"    class="px-6 py-4 text-slate-300 hover:text-nm-cyan hover:bg-slate-800/60 text-sm font-medium transition-colors">Contacto</a>
            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" id="mobile-login-btn"
               class="px-6 py-4 text-slate-300 hover:text-nm-cyan hover:bg-slate-800/60 text-sm font-medium transition-colors">
                @auth Ir al panel @else Iniciar sesión (equipo interno) @endauth
            </a>
            <div class="p-4">
                <a href="#contacto"
                   class="block text-center bg-nm-cyan hover:bg-cyan-400 text-white text-sm font-semibold font-jakarta px-5 py-3 rounded-xl transition-all">
                    Agendar Reunión Estratégica →
                </a>
            </div>
        </nav>
    </div>
</header>
