

<section id="jorge" class="bg-white py-24 lg:py-32 relative overflow-hidden">

    {{-- ── Subtle background ── --}}
    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent pointer-events-none"></div>
    <div class="absolute -top-20 -right-20 w-72 h-72 bg-nm-cyan/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-5 lg:px-10">

        {{-- ── Section Header ── --}}
        <div class="text-center max-w-3xl mx-auto mb-12 fade-in-up">
            <span class="inline-block bg-nm-cyan/10 border border-nm-cyan/30 text-nm-cyan text-xs font-semibold font-jakarta
                         px-4 py-1.5 rounded-full tracking-wider uppercase mb-5">
                Contenido & Comunidad
            </span>
            <h2 class="font-jakarta font-black text-3xl sm:text-4xl lg:text-5xl text-nm-dark leading-[1.15] tracking-tight mb-5">
                Jorge en <span class="text-gradient-cyan">Acción</span>
            </h2>
            <p class="text-slate-500 text-lg leading-relaxed">
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quod.
            </p>
        </div>

        {{-- ── Tab Navigation ── --}}
        <div class="flex justify-center mb-10 fade-in-up fade-delay-1">
            <div class="inline-flex bg-slate-100 rounded-2xl p-1.5 gap-1">
                <button class="tab-btn active flex items-center gap-2.5 px-5 py-2.5 rounded-xl text-sm font-semibold font-jakarta transition-all"
                        onclick="switchTab('youtube', this)">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                    </svg>
                    YouTube
                </button>
                <button class="tab-btn flex items-center gap-2.5 px-5 py-2.5 rounded-xl text-sm font-semibold font-jakarta text-slate-500 hover:text-slate-800 hover:bg-white transition-all"
                        onclick="switchTab('instagram', this)">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                    </svg>
                    Instagram
                </button>
                <button class="tab-btn flex items-center gap-2.5 px-5 py-2.5 rounded-xl text-sm font-semibold font-jakarta text-slate-500 hover:text-slate-800 hover:bg-white transition-all"
                        onclick="switchTab('linkedin', this)">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                    LinkedIn
                </button>
            </div>
        </div>

        {{-- ═══════════════════════════════════════
            TAB CONTENT: YouTube
        ══════════════════════════════════════ --}}
        <div id="tab-youtube" class="tab-content active fade-in-up fade-delay-2">
            {{-- Estructura de los cajones (sin videos linkeados ni información genérica) --}}
            <div class="grid md:grid-cols-3 gap-6">
                @for($i = 1; $i <= 3; $i++)
                <div class="group bg-white border border-slate-200 hover:border-slate-300 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300">
                    {{-- Contenedor de Video (Estructura placeholder sin video linkeado) --}}
                    <div class="aspect-video w-full bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 flex flex-col items-center justify-center p-6 text-center select-none relative">
                        <div class="w-14 h-14 rounded-full bg-white/10 border border-white/15 flex items-center justify-center shadow-inner group-hover:scale-105 transition-transform duration-300">
                            <svg class="w-6 h-6 text-slate-300/80 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Estructura de contenido (Slots limpios sin datos genéricos) --}}
                    <div class="p-5 space-y-4">
                        {{-- Tag de categoría y duración --}}
                        <div class="flex items-center justify-between">
                            <div class="h-4.5 w-24 bg-slate-100 rounded-full border border-slate-200/60"></div>
                            <div class="h-3.5 w-12 bg-slate-100 rounded"></div>
                        </div>

                        {{-- Título y descripción --}}
                        <div class="space-y-2">
                            <div class="h-5 w-4/5 bg-slate-100 rounded"></div>
                            <div class="h-3.5 w-full bg-slate-100/70 rounded"></div>
                        </div>

                        {{-- Metadata inferior --}}
                        <div class="pt-2 flex items-center gap-2 border-t border-slate-100">
                            <div class="w-4 h-4 rounded-full bg-slate-100"></div>
                            <div class="h-3 w-20 bg-slate-100 rounded"></div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>

            <div class="text-center mt-8">
                <a href="https://youtube.com/@neuromanagement" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 border border-slate-200 hover:border-red-400/50 text-slate-500 hover:text-red-500
                          text-sm font-medium px-6 py-3 rounded-xl transition-all duration-300 hover:bg-red-50">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                    </svg>
                    Ver canal en YouTube →
                </a>
            </div>
        </div>

        {{-- ═══════════════════════════════════════
            TAB CONTENT: Instagram
        ══════════════════════════════════════ --}}
        <div id="tab-instagram" class="tab-content">
            {{-- Estructura de los cajones de publicaciones (sin imágenes ni textos genéricos) --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-8">
                @for($i = 1; $i <= 6; $i++)
                <div class="group bg-white border border-slate-200 hover:border-slate-300 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 aspect-square flex flex-col items-center justify-center p-4 relative select-none">
                    {{-- Fondo sutil y placeholder de imagen --}}
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200/60 border border-slate-200/80 flex items-center justify-center text-slate-400 group-hover:scale-105 group-hover:text-pink-500 group-hover:border-pink-200 transition-all duration-300">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                        </svg>
                    </div>
                </div>
                @endfor
            </div>

            {{-- CTA Instagram --}}
            <div class="text-center">
                <a href="https://instagram.com/jorgevargas.nm" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 border border-slate-200 hover:border-pink-400/50 text-slate-500 hover:text-pink-600
                          text-sm font-medium px-6 py-3 rounded-xl transition-all duration-300 hover:bg-pink-50">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                    </svg>
                    Ver perfil en Instagram →
                </a>
            </div>
        </div>

        {{-- ═══════════════════════════════════════
            TAB CONTENT: LinkedIn
        ══════════════════════════════════════ --}}
        <div id="tab-linkedin" class="tab-content">
            {{-- Estructura de los cajones de publicaciones (sin textos ni datos genéricos) --}}
            <div class="max-w-2xl mx-auto space-y-4 mb-8">
                @for($i = 1; $i <= 3; $i++)
                <div class="group bg-white border border-slate-200 hover:border-slate-300 rounded-2xl p-6 transition-all duration-300 shadow-sm hover:shadow-md">
                    {{-- Cabecera del post (Avatar skeleton + Nombre/Fecha skeleton) --}}
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200/60 flex-shrink-0 flex items-center justify-center text-slate-300">
                            <svg class="w-5 h-5 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                        <div class="space-y-1.5 flex-1">
                            <div class="h-4 w-32 bg-slate-100 rounded"></div>
                            <div class="h-3 w-20 bg-slate-100/70 rounded"></div>
                        </div>
                    </div>

                    {{-- Cuerpo del post (Estructura de líneas de texto) --}}
                    <div class="space-y-2 mb-5">
                        <div class="h-4 w-4/5 bg-slate-100 rounded"></div>
                        <div class="h-3.5 w-full bg-slate-100/70 rounded"></div>
                        <div class="h-3.5 w-2/3 bg-slate-100/60 rounded"></div>
                    </div>

                    {{-- Pie del post (Métricas e interacción) --}}
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-1.5">
                                <div class="w-3.5 h-3.5 rounded-full bg-slate-100"></div>
                                <div class="h-3 w-10 bg-slate-100 rounded"></div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <div class="w-3.5 h-3.5 rounded-full bg-slate-100"></div>
                                <div class="h-3 w-10 bg-slate-100 rounded"></div>
                            </div>
                        </div>
                        <div class="h-3 w-16 bg-slate-100 rounded"></div>
                    </div>
                </div>
                @endfor
            </div>

            {{-- CTA LinkedIn --}}
            <div class="text-center">
                <a href="https://linkedin.com/in/jorgevargas-nm" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 border border-slate-200 hover:border-blue-400/50 text-slate-500 hover:text-blue-600
                          text-sm font-medium px-6 py-3 rounded-xl transition-all duration-300 hover:bg-blue-50">
                    <svg class="w-4 h-4 text-[#0077B5]" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                    Conectar en LinkedIn →
                </a>
            </div>
        </div>

    </div>

    {{-- ── Tab switching JS ── --}}
    <script>
        function switchTab(tab, btn) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('active');
                b.classList.add('text-slate-500');
                b.classList.remove('text-white');
            });
            document.getElementById('tab-' + tab).classList.add('active');
            btn.classList.add('active');
            btn.classList.remove('text-slate-500');
        }
    </script>
</section>
