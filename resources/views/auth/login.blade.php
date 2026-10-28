<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Acceso al sistema administrativo de NeuroManagement Chile. Uso exclusivo del equipo interno.">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar sesión | NeuroManagement Chile</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='6' fill='%230f172a'/><text x='50%25' y='50%25' dominant-baseline='central' text-anchor='middle' font-size='14' font-family='sans-serif' font-weight='bold' fill='%2306b6d4'>NM</text></svg>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'jakarta': ['"Plus Jakarta Sans"', 'sans-serif'],
                        'inter':   ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'nm-darkest': '#0a1120',
                        'nm-dark':    '#0f172a',
                        'nm-dark2':   '#1e293b',
                        'nm-cyan':    '#06b6d4',
                        'nm-cyan2':   '#0891b2',
                        'nm-cyan3':   '#0e7490',
                    },
                }
            }
        }
    </script>

    <style>
        html, body { background-color: #0a1120; }
        body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; background-color: #0a1120; }
        .font-jakarta { font-family: 'Plus Jakarta Sans', sans-serif !important; }

        .text-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #67e8f9 50%, #06b6d4 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .gradient-border {
            border: 1px solid transparent;
            background: linear-gradient(rgba(15,23,42,.92), rgba(15,23,42,.92)) padding-box,
                        linear-gradient(135deg, rgba(6,182,212,.55), rgba(8,145,178,.15) 60%, rgba(6,182,212,.35)) border-box;
        }
        .glow-cyan-sm { box-shadow: 0 0 20px rgba(6,182,212,.25); }

        /* Inputs (versión oscura de .nm-input) */
        .nm-field {
            width: 100%; padding: .8rem 1rem .8rem 2.75rem;
            background: rgba(30,41,59,.6);
            border: 1.5px solid rgba(71,85,105,.6); border-radius: .65rem;
            font-size: .925rem; color: #f1f5f9;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        .nm-field::placeholder { color: #64748b; }
        .nm-field:hover { border-color: rgba(100,116,139,.9); }
        .nm-field:focus {
            outline: none; border-color: #06b6d4; background: rgba(30,41,59,.9);
            box-shadow: 0 0 0 4px rgba(6,182,212,.15);
        }
        .nm-field.error { border-color: #f87171; box-shadow: 0 0 0 4px rgba(248,113,113,.12); }

        /* Foco visible accesible para navegación por teclado */
        .focus-ring:focus { outline: none; }
        .focus-ring:focus-visible { outline: 2px solid #22d3ee; outline-offset: 3px; border-radius: .4rem; }

        /* Checkbox personalizado */
        .nm-check {
            appearance: none; width: 1.1rem; height: 1.1rem; flex-shrink: 0;
            border: 1.5px solid #475569; border-radius: .3rem; background: rgba(30,41,59,.6);
            display: grid; place-content: center; cursor: pointer; transition: all .2s;
        }
        .nm-check::before {
            content: ''; width: .6rem; height: .6rem; transform: scale(0); transition: transform .15s ease-in-out;
            clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%); background: white;
        }
        .nm-check:checked { background: #06b6d4; border-color: #06b6d4; }
        .nm-check:checked::before { transform: scale(1); }
        .nm-check:focus-visible { outline: 2px solid #22d3ee; outline-offset: 2px; }

        /* Animaciones */
        @keyframes rise { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
        .rise { animation: rise .7s cubic-bezier(.22,1,.36,1) both; }
        .rise-2 { animation-delay: .12s; }
        @keyframes shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
        .btn-shimmer { background-size: 200% 100%; }
        .btn-shimmer:hover { animation: shimmer 2.2s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner { animation: spin .8s linear infinite; }

        @media (prefers-reduced-motion: reduce) {
            .rise, .btn-shimmer:hover, .spinner { animation: none !important; }
        }
    </style>
</head>
<body class="bg-[#0a1120] text-slate-100 min-h-screen">

<a href="#login-form" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 bg-nm-cyan text-white px-4 py-2 rounded-lg font-semibold">
    Saltar al formulario
</a>

<div class="relative min-h-screen grid lg:grid-cols-2 overflow-hidden bg-[#0a1120]">

    {{-- ── Panel de marca (escritorio) ── --}}
    <aside class="relative hidden lg:flex flex-col justify-between p-12 xl:p-16 border-r border-slate-800/70 bg-[#0a1120]">
        <a href="{{ route('home') }}" class="focus-ring flex items-center gap-3 group w-fit" aria-label="NeuroManagement Chile - Volver al sitio">
            <div class="w-11 h-11 bg-nm-cyan rounded-xl flex items-center justify-center font-bold text-white text-sm font-jakarta glow-cyan-sm group-hover:bg-cyan-400 transition-colors">NM</div>
            <div class="leading-tight">
                <span class="block text-white font-jakarta font-bold text-base tracking-tight">NeuroManagement</span>
                <span class="block text-nm-cyan text-[11px] font-medium tracking-widest uppercase">Chile</span>
            </div>
        </a>

        <div class="rise max-w-lg">
            <span class="inline-flex items-center gap-2 text-xs font-semibold tracking-widest uppercase text-nm-cyan bg-nm-cyan/10 border border-nm-cyan/25 px-3 py-1.5 rounded-full mb-6">
                <span class="w-1.5 h-1.5 rounded-full bg-nm-cyan"></span>
                Plataforma interna
            </span>
            <p class="font-jakarta text-4xl xl:text-5xl font-extrabold leading-tight tracking-tight">
                Gestión estratégica con <span class="text-gradient">claridad y control.</span>
            </p>
            <p class="mt-5 text-slate-400 text-lg leading-relaxed">
                Administra programas, cohortes, participantes y resultados desde un solo lugar.
            </p>

            <ul class="mt-10 space-y-4 text-slate-300">
                @foreach ([
                    ['M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.040A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'Acceso seguro y exclusivo para el equipo'],
                    ['M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'Indicadores y reportes en tiempo real'],
                    ['M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'Seguimiento integral de participantes'],
                ] as [$icon, $text])
                    <li class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-nm-dark2/80 border border-slate-700/60 flex items-center justify-center text-nm-cyan" aria-hidden="true">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon }}"/></svg>
                        </span>
                        {{ $text }}
                    </li>
                @endforeach
            </ul>
        </div>

        <p class="text-sm text-slate-500">&copy; {{ date('Y') }} NeuroManagement Chile. Todos los derechos reservados.</p>
    </aside>

    {{-- ── Formulario ── --}}
    <main class="relative flex items-center justify-center px-5 py-12 sm:px-8 bg-[#0a1120]">
        <div class="w-full max-w-md rise rise-2">

            {{-- Logo (móvil/tablet) --}}
            <a href="{{ route('home') }}" class="focus-ring lg:hidden flex items-center justify-center gap-3 mb-8 w-fit mx-auto" aria-label="NeuroManagement Chile - Volver al sitio">
                <div class="w-11 h-11 bg-nm-cyan rounded-xl flex items-center justify-center font-bold text-white text-sm font-jakarta glow-cyan-sm">NM</div>
                <div class="leading-tight">
                    <span class="block text-white font-jakarta font-bold text-base tracking-tight">NeuroManagement</span>
                    <span class="block text-nm-cyan text-[11px] font-medium tracking-widest uppercase">Chile</span>
                </div>
            </a>

            <div class="gradient-border backdrop-blur-xl rounded-2xl shadow-2xl shadow-black/40 p-7 sm:p-9">
                <header class="mb-7">
                    <h1 class="font-jakarta text-2xl sm:text-[1.7rem] font-bold tracking-tight text-white">Acceso al sistema administrativo</h1>
                    <p class="mt-2 text-sm text-slate-400">Ingresa con tu cuenta del equipo NeuroManagement Chile.</p>
                </header>

                @if (session('status'))
                    <div class="mb-5 flex gap-3 rounded-lg border border-nm-cyan/30 bg-nm-cyan/10 px-4 py-3 text-sm text-cyan-100" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 flex items-start gap-3 rounded-lg border border-red-400/30 bg-red-500/10 px-4 py-3 text-sm text-red-200" role="alert" aria-live="assertive">
                        <svg class="w-5 h-5 mt-px flex-shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form id="login-form" method="POST" action="{{ route('login.attempt') }}" class="space-y-5" novalidate>
                    @csrf

                    {{-- Correo --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-200 mb-2">Correo electrónico</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <input id="email" name="email" type="email" value="{{ old('email') }}"
                                   class="nm-field @error('email') error @enderror"
                                   placeholder="nombre@neuromanagement.cl"
                                   autocomplete="username" inputmode="email" required autofocus
                                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                        </div>
                        @error('email')
                            <p id="email-error" class="mt-1.5 text-xs text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-200 mb-2">Contraseña</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <input id="password" name="password" type="password"
                                   class="nm-field !pr-12 @error('password') error @enderror"
                                   placeholder="••••••••"
                                   autocomplete="current-password" required
                                   @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                            <button type="button" id="toggle-password"
                                    class="focus-ring absolute right-2 top-1/2 -translate-y-1/2 p-2 rounded-md text-slate-400 hover:text-nm-cyan hover:bg-slate-700/40 transition-colors"
                                    aria-controls="password" aria-pressed="false" aria-label="Mostrar contraseña">
                                <svg id="icon-eye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg id="icon-eye-off" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <p id="password-error" class="mt-1.5 text-xs text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Recordarme / Olvidé --}}
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <label for="remember" class="flex items-center gap-2.5 text-sm text-slate-300 cursor-pointer select-none">
                            <input id="remember" name="remember" type="checkbox" value="1" class="nm-check" @checked(old('remember'))>
                            Recordarme
                        </label>
                        <a href="{{ Route::has('password.request') ? route('password.request') : '#' }}"
                           id="forgot-password-link"
                           class="focus-ring text-sm font-medium text-nm-cyan hover:text-cyan-300 underline-offset-4 hover:underline transition-colors">
                            Olvidé mi contraseña
                        </a>
                    </div>

                    {{-- Enviar --}}
                    <button type="submit" id="login-submit"
                            class="focus-ring btn-shimmer group relative w-full inline-flex items-center justify-center gap-2 rounded-lg px-5 py-3.5
                                   font-jakarta font-semibold text-white
                                   bg-[linear-gradient(110deg,#0891b2_0%,#06b6d4_40%,#67e8f9_50%,#06b6d4_60%,#0891b2_100%)]
                                   shadow-lg shadow-cyan-500/25 hover:shadow-cyan-500/40 hover:-translate-y-0.5 active:translate-y-0
                                   transition-all duration-300 disabled:opacity-70 disabled:cursor-wait disabled:translate-y-0">
                        <svg id="login-spinner" class="w-5 h-5 spinner hidden" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path fill="currentColor" class="opacity-90" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                        <span id="login-label">Iniciar sesión</span>
                        <svg id="login-arrow" class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </button>
                </form>

                <p class="mt-7 pt-6 border-t border-slate-700/60 text-xs text-slate-500 text-center leading-relaxed">
                    Acceso exclusivo para el equipo interno. Si necesitas una cuenta, solicítala al administrador del sistema.
                </p>
            </div>

            <p class="mt-6 text-center">
                <a href="{{ route('home') }}" class="focus-ring text-sm text-slate-400 hover:text-nm-cyan transition-colors">← Volver al sitio</a>
            </p>
        </div>
    </main>
</div>

<script>
    (() => {
        const input  = document.getElementById('password');
        const toggle = document.getElementById('toggle-password');
        const eye    = document.getElementById('icon-eye');
        const eyeOff = document.getElementById('icon-eye-off');

        toggle.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', String(show));
            toggle.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
            eye.classList.toggle('hidden', show);
            eyeOff.classList.toggle('hidden', !show);
        });

        // Estado de carga y prevención de doble envío
        const form = document.getElementById('login-form');
        form.addEventListener('submit', () => {
            const btn = document.getElementById('login-submit');
            btn.disabled = true;
            document.getElementById('login-spinner').classList.remove('hidden');
            document.getElementById('login-arrow').classList.add('hidden');
            document.getElementById('login-label').textContent = 'Ingresando…';
        });
    })();
</script>
</body>
</html>
