

<section id="contacto" class="bg-slate-50 py-24 lg:py-32 relative overflow-hidden">

    {{-- ── Background ── --}}
    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent pointer-events-none"></div>
    <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-nm-cyan/5 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -top-20 -left-20 w-72 h-72 bg-nm-cyan/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-5 lg:px-10">

        {{-- ── Header ── --}}
        <div class="text-center max-w-3xl mx-auto mb-14 fade-in-up">
            <span class="inline-block bg-nm-cyan/10 border border-nm-cyan/30 text-nm-cyan text-xs font-semibold font-jakarta
                         px-4 py-1.5 rounded-full tracking-wider uppercase mb-5">
                Primer Paso
            </span>
            <h2 class="font-jakarta font-black text-3xl sm:text-4xl lg:text-5xl text-nm-dark leading-[1.15] tracking-tight mb-5">
                Agenda tu Reunión<br>
                <span class="text-gradient-cyan">Estratégica Gratuita</span>
            </h2>
            <p class="text-slate-500 text-lg leading-relaxed">
                Una conversación de 45 minutos donde analizamos tu situación actual y exploramos cómo Neuro Management puede transformar tu organización.
            </p>
        </div>

        <div class="grid lg:grid-cols-5 gap-10 lg:gap-16 items-start">

            {{-- ── LEFT: Info block ── --}}
            <div class="lg:col-span-2 space-y-7 fade-in-up fade-delay-1">

                {{-- Jorge card --}}
                <div class="bg-nm-dark rounded-2xl p-6 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-nm-cyan/10 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="relative flex items-start gap-4">
                        <div>
                            <div class="font-jakarta font-bold text-white text-lg leading-tight">Jorge Vargas</div>
                            <div class="text-nm-cyan text-sm font-medium mb-2">Fundador · Neuro Management</div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse flex-shrink-0"></span>
                                <span class="text-slate-300 text-xs">Disponible para reuniones este mes</span>
                            </div>
                        </div>
                    </div>
                    <p class="relative text-slate-400 text-sm leading-relaxed mt-4">
                        "Cada transformación comienza con una conversación honesta. Cuéntame dónde estás y hacia dónde quieres ir."
                    </p>
                </div>

                {{-- What to expect --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-6">
                    <h3 class="font-jakarta font-bold text-nm-dark text-base mb-4">¿Qué esperar de la reunión?</h3>
                    <ul class="space-y-3">
                        @foreach([
                            'Diagnóstico express de tu situación actual',
                            'Identificación de los principales cuellos de botella',
                            'Propuesta de enfoque inicial sin compromiso',
                            'Respuestas claras a tus preguntas más urgentes',
                        ] as $item)
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 bg-nm-cyan/10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3 h-3 text-nm-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="text-slate-600 text-sm leading-relaxed">{{ $item }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Contact details --}}
                <div class="space-y-3">
                    @foreach([
                        ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>', 'label' => 'Email', 'value' => 'jorge@neuromanagement.com'],
                        ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>', 'label' => 'WhatsApp', 'value' => '+56 9 8765 4321'],
                        ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>', 'label' => 'Ubicación', 'value' => 'Santiago · Chile (Remoto mundial)'],
                    ] as $contact)
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-nm-cyan/10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-nm-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $contact['icon'] !!}
                            </svg>
                        </div>
                        <div>
                            <div class="text-slate-400 text-[10px] uppercase tracking-widest font-medium">{{ $contact['label'] }}</div>
                            <div class="text-nm-dark text-sm font-medium">{{ $contact['value'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ── RIGHT: Form ── --}}
            <div class="lg:col-span-3 fade-in-up fade-delay-2">

                {{-- Success message --}}
                @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-2xl p-5 mb-6 flex items-start gap-3">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-jakarta font-bold text-green-800 text-sm">{{ session('success') }}</div>
                        @if(session('nombre'))
                        <p class="text-green-600 text-xs mt-0.5">Gracias {{ session('nombre') }}, revisaremos tu mensaje pronto.</p>
                        @endif
                    </div>
                </div>
                @endif

                <div class="bg-white border border-slate-200 rounded-2xl p-7 lg:p-9 shadow-sm">
                    <h3 class="font-jakarta font-bold text-nm-dark text-xl mb-6">Cuéntanos sobre tu organización</h3>

                    <form id="contact-form" action="{{ route('contacto.send') }}" method="POST" class="space-y-5" novalidate>
                        @csrf

                        {{-- Nombre + Email --}}
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label for="nombre" class="block text-slate-600 text-xs font-semibold uppercase tracking-wider mb-1.5">
                                    Nombre completo <span class="text-red-400">*</span>
                                </label>
                                <input type="text" id="nombre" name="nombre"
                                       value="{{ old('nombre') }}"
                                       placeholder="Ej: María González"
                                       class="nm-input {{ $errors->has('nombre') ? 'error' : '' }}"
                                       required>
                                @error('nombre')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="email" class="block text-slate-600 text-xs font-semibold uppercase tracking-wider mb-1.5">
                                    Correo electrónico <span class="text-red-400">*</span>
                                </label>
                                <input type="email" id="email" name="email"
                                       value="{{ old('email') }}"
                                       placeholder="Ej: maria@empresa.com"
                                       class="nm-input {{ $errors->has('email') ? 'error' : '' }}"
                                       required>
                                @error('email')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Empresa + Cargo --}}
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label for="empresa" class="block text-slate-600 text-xs font-semibold uppercase tracking-wider mb-1.5">
                                    Empresa <span class="text-red-400">*</span>
                                </label>
                                <input type="text" id="empresa" name="empresa"
                                       value="{{ old('empresa') }}"
                                       placeholder="Ej: Grupo Industrial S.A."
                                       class="nm-input {{ $errors->has('empresa') ? 'error' : '' }}"
                                       required>
                                @error('empresa')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="cargo" class="block text-slate-600 text-xs font-semibold uppercase tracking-wider mb-1.5">
                                    Tu cargo <span class="text-red-400">*</span>
                                </label>
                                <input type="text" id="cargo" name="cargo"
                                       value="{{ old('cargo') }}"
                                       placeholder="Ej: CEO / Gerente General"
                                       class="nm-input {{ $errors->has('cargo') ? 'error' : '' }}"
                                       required>
                                @error('cargo')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Tamaño empresa (select) --}}
                        <div>
                            <label for="tamano" class="block text-slate-600 text-xs font-semibold uppercase tracking-wider mb-1.5">
                                Tamaño de tu organización
                            </label>
                            <select id="tamano" name="tamano" class="nm-input cursor-pointer">
                                <option value="">Selecciona una opción...</option>
                                <option value="1-10" {{ old('tamano') === '1-10' ? 'selected' : '' }}>1–10 colaboradores</option>
                                <option value="11-50" {{ old('tamano') === '11-50' ? 'selected' : '' }}>11–50 colaboradores</option>
                                <option value="51-200" {{ old('tamano') === '51-200' ? 'selected' : '' }}>51–200 colaboradores</option>
                                <option value="201-500" {{ old('tamano') === '201-500' ? 'selected' : '' }}>201–500 colaboradores</option>
                                <option value="500+" {{ old('tamano') === '500+' ? 'selected' : '' }}>Más de 500 colaboradores</option>
                            </select>
                        </div>

                        {{-- Mensaje --}}
                        <div>
                            <label for="mensaje" class="block text-slate-600 text-xs font-semibold uppercase tracking-wider mb-1.5">
                                ¿Cuál es tu principal desafío hoy? <span class="text-red-400">*</span>
                            </label>
                            <textarea id="mensaje" name="mensaje" rows="4"
                                      placeholder="Cuéntanos brevemente qué está pasando en tu organización y qué esperas lograr con la consultoría..."
                                      class="nm-input resize-none {{ $errors->has('mensaje') ? 'error' : '' }}"
                                      required>{{ old('mensaje') }}</textarea>
                            @error('mensaje')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Privacy note --}}
                        <p class="text-slate-400 text-xs leading-relaxed">
                            🔒 Tu información es completamente confidencial. No compartimos datos con terceros.
                        </p>

                        {{-- Submit --}}
                        <button type="submit" id="submit-btn"
                                class="w-full group inline-flex items-center justify-center gap-2.5 bg-nm-dark hover:bg-nm-dark2 text-white
                                       font-semibold font-jakarta text-base px-8 py-4 rounded-xl transition-all duration-300
                                       hover:shadow-xl hover:shadow-slate-900/30 border border-transparent hover:border-nm-cyan/20">
                            <span>Agendar mi Reunión Estratégica Gratuita</span>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </button>

                        <div class="flex items-center justify-center gap-4 text-slate-400 text-xs">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Sin costo
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Sin compromiso
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Respuesta en 24h
                            </span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Form loading state JS ── --}}
    <script>
        document.getElementById('contact-form')?.addEventListener('submit', function() {
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span>Enviando mensaje...</span>
            `;
        });
    </script>
</section>
