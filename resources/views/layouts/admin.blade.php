@php
    $navSections = \App\Support\AdminNavigation::sections();
    $authUser    = auth()->user();
    $userInitials = collect(preg_split('/\s+/', trim($authUser?->name ?? '')))
        ->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'U';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') | Administración NeuroManagement Chile</title>

    {{-- Aplica el estado contraído antes de pintar (evita parpadeo) --}}
    <script>
        try { if (localStorage.getItem('nm.sidebar') === 'collapsed') document.documentElement.classList.add('sb-collapsed'); } catch (e) {}
    </script>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='6' fill='%230f172a'/><text x='50%25' y='50%25' dominant-baseline='central' text-anchor='middle' font-size='14' font-family='sans-serif' font-weight='bold' fill='%2306b6d4'>NM</text></svg>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Misma configuración Tailwind (CDN) que el sitio público --}}
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
        :root { --sb-w: 256px; --sb-w-collapsed: 76px; --topbar-h: 64px; }
        *, *::before, *::after { box-sizing: border-box; }
        html, body { overflow-x: hidden; background-color: #0a1120; }
        body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; background-color: #0a1120; color: #e2e8f0; }
        .font-jakarta { font-family: 'Plus Jakarta Sans', sans-serif !important; }
        .text-gradient-cyan { background: linear-gradient(135deg, #22d3ee, #06b6d4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

        /* ── Foco visible (teclado) ── */
        .admin-focus:focus { outline: none; }
        .admin-focus:focus-visible, a:focus-visible, button:focus-visible, select:focus-visible, input:focus-visible {
            outline: 2px solid #22d3ee; outline-offset: 2px;
        }

        /* ── Sidebar ── */
        #admin-sidebar {
            position: fixed; top: 0; bottom: 0; left: 0; z-index: 50;
            width: var(--sb-w);
            transform: translateX(-100%);
            transition: transform .28s cubic-bezier(.22,1,.36,1), width .22s ease;
            background: #0b1222;
            border-right: 1px solid #1e293b;
        }
        #admin-sidebar.is-open { transform: translateX(0); }
        .sb-scroll { scrollbar-width: thin; scrollbar-color: #1e293b transparent; }
        .sb-scroll::-webkit-scrollbar { width: 6px; }
        .sb-scroll::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 3px; }

        .sb-link {
            position: relative; display: flex; align-items: center; gap: .75rem;
            padding: .55rem .75rem; border-radius: .5rem;
            font-size: .875rem; font-weight: 500; color: #cbd5e1;
            transition: background .15s, color .15s;
        }
        .sb-link svg { width: 1.125rem; height: 1.125rem; flex-shrink: 0; color: #64748b; transition: color .15s; }
        a.sb-link:hover { background: #131d31; color: #fff; }
        a.sb-link:hover svg { color: #cbd5e1; }
        .sb-link.is-active { background: rgba(6,182,212,.1); color: #fff; }
        .sb-link.is-active svg { color: #22d3ee; }
        .sb-link.is-active::before {
            content: ''; position: absolute; left: -.75rem; top: .5rem; bottom: .5rem; width: 2px;
            border-radius: 0 2px 2px 0; background: #06b6d4;
        }
        .sb-link.is-disabled { color: #64748b; cursor: not-allowed; }
        .sb-link.is-disabled svg { color: #334155; }
        /* "Próximamente": se indica con el tono atenuado, el tooltip y el texto para lectores de pantalla */
        .sb-badge { display: none; }
        .sb-section-title { font-size: .68rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #475569; padding: 0 .75rem; margin-bottom: .35rem; }
        .sb-divider { display: none; height: 1px; margin: .25rem .9rem .6rem; background: #1e293b; }

        #admin-overlay { position: fixed; inset: 0; z-index: 45; background: rgba(2,6,23,.7); backdrop-filter: blur(3px); opacity: 0; pointer-events: none; transition: opacity .25s; }
        #admin-overlay.is-visible { opacity: 1; pointer-events: auto; }

        #admin-main { min-height: 100vh; display: flex; flex-direction: column; min-width: 0; background-color: #0a1120; transition: padding-left .22s ease; }
        #admin-content { background-color: #0a1120; }

        @media (min-width: 1024px) {
            #admin-sidebar { transform: none; box-shadow: none !important; }
            #admin-overlay { display: none; }
            #admin-main { padding-left: var(--sb-w); }
            .sb-mobile-only { display: none !important; }

            html.sb-collapsed #admin-sidebar { width: var(--sb-w-collapsed); }
            html.sb-collapsed #admin-main { padding-left: var(--sb-w-collapsed); }
            html.sb-collapsed .sb-label,
            html.sb-collapsed .sb-badge,
            html.sb-collapsed .sb-section-title,
            html.sb-collapsed .sb-brand-text { display: none; }
            html.sb-collapsed .sb-divider { display: block; }
            html.sb-collapsed .sb-link { justify-content: center; padding-left: 0; padding-right: 0; }
            html.sb-collapsed .sb-brand { justify-content: center; padding-left: 0; padding-right: 0; }
            html.sb-collapsed .sb-link.is-active::before { left: -.5rem; }
        }

        /* Tooltip para menú contraído (position: fixed para no ser recortado por el scroll) */
        #sb-tooltip {
            position: fixed; z-index: 60; pointer-events: none; opacity: 0; transform: translateX(-4px);
            transition: opacity .12s, transform .12s;
            padding: .4rem .65rem; border-radius: .45rem; font-size: .78rem; font-weight: 500; white-space: nowrap;
            color: #f1f5f9; background: #1e293b; border: 1px solid #334155;
        }
        #sb-tooltip.is-visible { opacity: 1; transform: translateX(0); }

        /* Menú de usuario */
        #user-menu { transform-origin: top right; transition: opacity .12s, transform .12s; }
        #user-menu[hidden] { display: block; opacity: 0; transform: scale(.96); pointer-events: none; visibility: hidden; }

        /* Superficies reutilizables del panel */
        .panel { background: #0f172a; border: 1px solid #1e293b; border-radius: .75rem; }

        @media (prefers-reduced-motion: reduce) {
            #admin-sidebar, #admin-main, #admin-overlay, #sb-tooltip, #user-menu { transition: none !important; }
        }
    </style>

    @yield('styles')
</head>
<body class="min-h-screen bg-[#0a1120]">

<a href="#admin-content"
   class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[70] bg-nm-cyan text-white px-4 py-2 rounded-lg font-semibold">
    Saltar al contenido
</a>

{{-- ════════════════ Barra lateral ════════════════ --}}
<aside id="admin-sidebar" class="flex flex-col" aria-label="Menú de administración">

    {{-- Marca --}}
    <div class="sb-brand flex items-center gap-3 h-16 px-5 border-b border-slate-800 shrink-0">
        <a href="{{ route('dashboard') }}" class="admin-focus flex items-center gap-3 min-w-0 rounded-lg" aria-label="NeuroManagement — Inicio del panel" data-tip="NeuroManagement">
            <span class="w-9 h-9 shrink-0 bg-nm-cyan rounded-lg flex items-center justify-center font-bold text-white text-sm font-jakarta">NM</span>
            <span class="sb-brand-text leading-tight min-w-0">
                <span class="block text-white font-jakarta font-bold text-[15px] tracking-tight truncate">NeuroManagement</span>
                <span class="block text-slate-500 text-xs font-medium">Administración</span>
            </span>
        </a>
        <button type="button" id="sidebar-close"
                class="sb-mobile-only ml-auto p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800"
                aria-label="Cerrar menú">
            <x-icon name="x-mark" />
        </button>
    </div>

    {{-- Navegación (con scroll propio) --}}
    <nav class="sb-scroll flex-1 overflow-y-auto overflow-x-hidden px-3 py-5 space-y-5" aria-label="Módulos">
        @foreach ($navSections as $section)
            <div>
                <p class="sb-section-title">{{ $section['title'] }}</p>
                @unless ($loop->first)<div class="sb-divider" aria-hidden="true"></div>@endunless
                <ul class="space-y-0.5">
                    @foreach ($section['items'] as $item)
                        <li>
                            @if ($item['available'])
                                <a href="{{ $item['url'] }}"
                                   class="sb-link admin-focus {{ $item['is_active'] ? 'is-active' : '' }}"
                                   data-tip="{{ $item['label'] }}"
                                   @if ($item['is_active']) aria-current="page" @endif>
                                    <x-icon :name="$item['icon']" />
                                    <span class="sb-label truncate">{{ $item['label'] }}</span>
                                </a>
                            @else
                                <span class="sb-link is-disabled" aria-disabled="true" title="Próximamente" data-tip="{{ $item['label'] }} · Próximamente">
                                    <x-icon :name="$item['icon']" />
                                    <span class="sb-label truncate">{{ $item['label'] }}</span>
                                    <span class="sb-badge">Próximamente</span>
                                    <span class="sr-only">(no disponible todavía)</span>
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    {{-- Pie: sitio web + cerrar sesión --}}
    <div class="shrink-0 border-t border-slate-800 p-3 space-y-0.5">
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="sb-link admin-focus" data-tip="Ver sitio web">
            <x-icon name="globe" />
            <span class="sb-label">Ver sitio web</span>
            <x-icon name="arrow-up-right" class="sb-label w-3.5 h-3.5 ml-auto" />
            <span class="sr-only">(se abre en una pestaña nueva)</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" id="sidebar-logout"
                    class="sb-link admin-focus w-full text-left hover:!bg-red-500/10 hover:!text-red-300 group" data-tip="Cerrar sesión">
                <x-icon name="logout" class="group-hover:!text-red-300" />
                <span class="sb-label">Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>

<div id="admin-overlay" aria-hidden="true"></div>
<div id="sb-tooltip" role="tooltip" aria-hidden="true"></div>

{{-- ════════════════ Contenido ════════════════ --}}
<div id="admin-main" class="bg-[#0a1120]">

    {{-- Barra superior --}}
    <header class="sticky top-0 z-30 h-16 flex items-center gap-3 px-4 sm:px-6 lg:px-8
                   bg-[#0a1120] border-b border-slate-800">
        <button type="button" id="sidebar-toggle"
                class="admin-focus p-2 -ml-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors"
                aria-controls="admin-sidebar" aria-expanded="false" aria-label="Abrir menú">
            <x-icon name="bars" class="w-6 h-6" />
        </button>

        {{-- Navegación de la pantalla: ruta hacia las páginas superiores. El título principal
             lo muestra el contenido; si la vista no define ruta, se usa page_title. --}}
        <div class="min-w-0 flex-1">
            @hasSection('breadcrumbs')
                @yield('breadcrumbs')
            @else
                <h1 class="text-base sm:text-lg font-semibold text-white truncate">@yield('page_title', 'Panel')</h1>
            @endif
        </div>

        @if ($authUser)
            <div class="relative ml-auto shrink-0">
                <button type="button" id="user-menu-button"
                        class="admin-focus flex items-center gap-3 rounded-lg pl-1.5 pr-2 py-1.5 hover:bg-slate-800/70 transition-colors"
                        aria-haspopup="menu" aria-expanded="false" aria-controls="user-menu">
                    <span class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-slate-100 text-xs font-semibold">{{ $userInitials }}</span>
                    <span class="hidden sm:block text-left leading-tight max-w-[180px]">
                        <span class="block text-sm font-semibold text-slate-100 truncate">{{ $authUser->name }}</span>
                        {{-- El sistema aún no asigna roles a las cuentas del panel: no se muestra rol. --}}
                        <span class="block text-xs text-slate-400 truncate">{{ $authUser->email }}</span>
                    </span>
                    <x-icon name="chevron-down" class="w-4 h-4 text-slate-400" />
                </button>

                <div id="user-menu" hidden role="menu" aria-labelledby="user-menu-button"
                     class="absolute right-0 mt-2 w-64 rounded-lg border border-slate-700 bg-[#0f172a] shadow-lg shadow-black/30 overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-800">
                        <p class="text-sm font-semibold text-white truncate">{{ $authUser->name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ $authUser->email }}</p>
                    </div>
                    {{-- No existe página de perfil todavía: solo se ofrece cerrar sesión. --}}
                    <form method="POST" action="{{ route('logout') }}" class="p-1.5">
                        @csrf
                        <button type="submit" role="menuitem" id="user-menu-logout"
                                class="admin-focus w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm text-slate-200 hover:bg-red-500/10 hover:text-red-300 transition-colors">
                            <x-icon name="logout" class="w-4 h-4" />
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </header>

    <main id="admin-content" class="flex-1 min-w-0 px-4 sm:px-6 lg:px-8 py-6 lg:py-8 bg-[#0a1120]" tabindex="-1">
        @if (session('status'))
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-nm-cyan/30 bg-nm-cyan/10 px-4 py-3 text-sm text-cyan-100" role="status">
                <x-icon name="info" class="w-5 h-5 text-nm-cyan" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script>
(() => {
    const html     = document.documentElement;
    const sidebar  = document.getElementById('admin-sidebar');
    const overlay  = document.getElementById('admin-overlay');
    const toggle   = document.getElementById('sidebar-toggle');
    const closeBtn = document.getElementById('sidebar-close');
    const tooltip  = document.getElementById('sb-tooltip');
    const desktop  = window.matchMedia('(min-width: 1024px)');

    /* ── Estado del botón según contexto ── */
    function syncToggle() {
        if (desktop.matches) {
            const collapsed = html.classList.contains('sb-collapsed');
            toggle.setAttribute('aria-expanded', String(!collapsed));
            toggle.setAttribute('aria-label', collapsed ? 'Expandir menú' : 'Contraer menú');
        } else {
            const open = sidebar.classList.contains('is-open');
            toggle.setAttribute('aria-expanded', String(open));
            toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
        }
    }

    /* ── Móvil: panel lateral ── */
    function openMobile() {
        sidebar.classList.add('is-open');
        overlay.classList.add('is-visible');
        document.body.style.overflow = 'hidden';
        syncToggle();
        (sidebar.querySelector('.sb-link.is-active') || sidebar.querySelector('a, button'))?.focus();
    }
    function closeMobile(returnFocus = true) {
        if (!sidebar.classList.contains('is-open')) return;
        sidebar.classList.remove('is-open');
        overlay.classList.remove('is-visible');
        document.body.style.overflow = '';
        syncToggle();
        if (returnFocus) toggle.focus();
    }

    /* ── Escritorio: contraer/expandir ── */
    function toggleCollapsed() {
        const collapsed = html.classList.toggle('sb-collapsed');
        try { localStorage.setItem('nm.sidebar', collapsed ? 'collapsed' : 'expanded'); } catch (e) {}
        hideTip();
        syncToggle();
    }

    toggle.addEventListener('click', () => {
        if (desktop.matches) toggleCollapsed();
        else sidebar.classList.contains('is-open') ? closeMobile() : openMobile();
    });
    closeBtn?.addEventListener('click', () => closeMobile());
    overlay.addEventListener('click', () => closeMobile());

    // Cerrar al seleccionar una opción (móvil)
    sidebar.querySelectorAll('a.sb-link').forEach(a => a.addEventListener('click', () => {
        if (!desktop.matches) closeMobile(false);
    }));

    // Al pasar a escritorio, limpiar estado móvil
    desktop.addEventListener('change', () => { closeMobile(false); hideTip(); syncToggle(); });

    // Mantener el foco dentro del panel móvil abierto
    sidebar.addEventListener('keydown', (e) => {
        if (e.key !== 'Tab' || desktop.matches || !sidebar.classList.contains('is-open')) return;
        const f = [...sidebar.querySelectorAll('a[href], button:not([disabled])')].filter(el => el.offsetParent !== null);
        if (!f.length) return;
        if (e.shiftKey && document.activeElement === f[0]) { e.preventDefault(); f[f.length - 1].focus(); }
        else if (!e.shiftKey && document.activeElement === f[f.length - 1]) { e.preventDefault(); f[0].focus(); }
    });

    /* ── Tooltips del menú contraído ── */
    function showTip(el) {
        if (!desktop.matches || !html.classList.contains('sb-collapsed')) return;
        const r = el.getBoundingClientRect();
        tooltip.textContent = el.dataset.tip;
        tooltip.style.left = (r.right + 12) + 'px';
        tooltip.style.top  = (r.top + r.height / 2) + 'px';
        tooltip.style.marginTop = '-' + (tooltip.offsetHeight / 2) + 'px';
        tooltip.classList.add('is-visible');
    }
    function hideTip() { tooltip.classList.remove('is-visible'); }
    sidebar.querySelectorAll('[data-tip]').forEach(el => {
        el.addEventListener('mouseenter', () => showTip(el));
        el.addEventListener('mouseleave', hideTip);
        el.addEventListener('focus', () => showTip(el));
        el.addEventListener('blur', hideTip);
    });
    sidebar.querySelector('nav')?.addEventListener('scroll', hideTip, { passive: true });

    /* ── Menú de usuario ── */
    const umBtn  = document.getElementById('user-menu-button');
    const umMenu = document.getElementById('user-menu');
    function setUserMenu(open) {
        if (!umBtn) return;
        umMenu.hidden = !open;
        umBtn.setAttribute('aria-expanded', String(open));
        if (open) umMenu.querySelector('[role="menuitem"]')?.focus();
    }
    umBtn?.addEventListener('click', (e) => { e.stopPropagation(); setUserMenu(umMenu.hidden); });
    document.addEventListener('click', (e) => { if (umMenu && !umMenu.hidden && !umMenu.contains(e.target)) setUserMenu(false); });

    /* ── Escape ── */
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (umMenu && !umMenu.hidden) { setUserMenu(false); umBtn.focus(); return; }
        closeMobile();
        hideTip();
    });

    syncToggle();
})();
</script>

@yield('scripts')
</body>
</html>
