<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Jorge Vargas — Consultoría Ejecutiva de Alto Nivel. Neuro Management: Transforma tu liderazgo, claridad estratégica y capacidad organizacional.">
    <meta name="keywords" content="consultoría ejecutiva, liderazgo, Jorge Vargas, Neuro Management, estrategia empresarial, gerentes, alta dirección">
    <meta name="author" content="Jorge Vargas — Neuro Management">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="index, follow">

    <title>@yield('title', 'Neuro Management | Jorge Vargas — Consultoría Ejecutiva')</title>

    <!-- Open Graph / Social -->
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="{{ config('app.url') }}">
    <meta property="og:title"       content="Neuro Management | Jorge Vargas">
    <meta property="og:description" content="Consultoría Ejecutiva de Alto Nivel para gerentes, dueños de empresa y alta dirección.">
    <meta property="og:image"       content="{{ config('app.url') }}/og-image.jpg">
    <meta name="twitter:card"       content="summary_large_image">

    <!-- Favicon placeholder -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='6' fill='%230f172a'/><text x='50%25' y='50%25' dominant-baseline='central' text-anchor='middle' font-size='14' font-family='sans-serif' font-weight='bold' fill='%2306b6d4'>NM</text></svg>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS Play CDN -->
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
                    screens: {
                        'xs': '480px',
                    },
                }
            }
        }
    </script>

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; overflow-x: hidden; }

        /* ── Typography helpers ── */
        .font-jakarta { font-family: 'Plus Jakarta Sans', sans-serif !important; }

        .text-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #67e8f9 50%, #06b6d4 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .text-gradient-cyan {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }

        /* ── Background patterns ── */
        .bg-grid-dark {
            background-color: #0a1120;
        }
        .bg-dots {
            display: none;
        }

        /* ── Navbar ── */
        #navbar { background: transparent; transition: background .5s ease, box-shadow .5s ease, border-color .5s ease; }
        #navbar.scrolled {
            background: rgba(10,17,32,.97);
            backdrop-filter: blur(24px);
            border-bottom: 1px solid rgba(6,182,212,.15);
            box-shadow: 0 4px 32px rgba(0,0,0,.5);
        }

        /* ── Scroll animations ── */
        .fade-in-up { opacity: 0; transform: translateY(40px); transition: opacity .75s ease, transform .75s ease; }
        .fade-in-up.visible { opacity: 1; transform: translateY(0); }
        .fade-delay-1 { transition-delay: .1s; }
        .fade-delay-2 { transition-delay: .2s; }
        .fade-delay-3 { transition-delay: .3s; }
        .fade-delay-4 { transition-delay: .4s; }
        .fade-delay-5 { transition-delay: .5s; }

        /* ── Card hover lift ── */
        .card-lift { transition: transform .35s cubic-bezier(.175,.885,.32,1.275), box-shadow .35s ease; }
        .card-lift:hover { transform: translateY(-10px); box-shadow: 0 24px 60px rgba(6,182,212,.15); }

        /* ── Glow effects ── */
        .glow-cyan { box-shadow: 0 0 40px rgba(6,182,212,.35); }
        .glow-cyan-sm { box-shadow: 0 0 20px rgba(6,182,212,.25); }

        /* ── Gradient borders ── */
        .gradient-border {
            border: 1px solid transparent;
            background: linear-gradient(#1e293b, #1e293b) padding-box,
                        linear-gradient(135deg, rgba(6,182,212,.6), rgba(8,145,178,.3)) border-box;
        }
        .gradient-border-light {
            border: 1px solid transparent;
            background: linear-gradient(white, white) padding-box,
                        linear-gradient(135deg, #06b6d4, #0891b2) border-box;
        }

        /* ── Video embed ── */
        .video-wrapper { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 12px; }
        .video-wrapper iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 12px; border: none; }

        /* ── Tab system ── */
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .tab-btn { transition: all .3s ease; }
        .tab-btn.active {
            background: linear-gradient(135deg, #06b6d4, #0891b2) !important;
            color: white !important;
            box-shadow: 0 4px 20px rgba(6,182,212,.4);
        }

        /* ── Testimonial slider ── */
        .slider-track { display: flex; transition: transform .55s cubic-bezier(.25,.46,.45,.94); }
        .slider-slide { min-width: 100%; }

        /* ── Stats counter ── */
        .stat-num { display: inline-block; }

        /* ── Instagram grid ── */
        .ig-card .ig-overlay {
            opacity: 0; transition: opacity .3s ease;
            background: linear-gradient(135deg, rgba(6,182,212,.85), rgba(8,145,178,.85));
        }
        .ig-card:hover .ig-overlay { opacity: 1; }

        /* ── Form ── */
        .nm-input {
            width: 100%; padding: .75rem 1rem; background: white;
            border: 1.5px solid #e2e8f0; border-radius: .5rem;
            font-size: .875rem; color: #0f172a; transition: border-color .2s, box-shadow .2s;
            font-family: 'Inter', sans-serif;
        }
        .nm-input::placeholder { color: #94a3b8; }
        .nm-input:focus {
            outline: none; border-color: #06b6d4;
            box-shadow: 0 0 0 3px rgba(6,182,212,.12);
        }
        .nm-input.error { border-color: #f87171; box-shadow: 0 0 0 3px rgba(248,113,113,.1); }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #06b6d4; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #0891b2; }

        /* ── Hero blob ── */
        @keyframes blob { 0%,100%{border-radius:60% 40% 30% 70% / 60% 30% 70% 40%} 50%{border-radius:30% 60% 70% 40% / 50% 60% 30% 60%} }
        .blob { animation: blob 9s ease-in-out infinite; }

        /* ── Float ── */
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-14px)} }
        .floating { animation: float 5s ease-in-out infinite; }
        .floating-slow { animation: float 7s ease-in-out infinite; }

        /* ── Pulse ring ── */
        @keyframes pulse-ring { 0%{transform:scale(.8);opacity:1} 100%{transform:scale(2);opacity:0} }

        /* ── Shimmer ── */
        @keyframes shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
        .shimmer { background: linear-gradient(90deg, transparent, rgba(6,182,212,.08), transparent); background-size: 200% 100%; animation: shimmer 3.5s infinite; }

        /* ── Hero section diagonal ── */
        .hero-clip { clip-path: polygon(0 0, 100% 0, 100% 92%, 0 100%); }

        /* ── Section spacing ── */
        section { position: relative; }
    </style>

    @yield('styles')
</head>
<body class="bg-white text-slate-900">

    @include('partials.navbar')

    <main id="main-content">
        @yield('content')
    </main>

    @include('partials.footer')

    <!-- ── Global JavaScript ── -->
    <script>
        (() => {
            // Navbar scroll effect
            const nav = document.getElementById('navbar');
            window.addEventListener('scroll', () => {
                nav?.classList.toggle('scrolled', window.scrollY > 60);
            }, { passive: true });

            // Mobile menu
            document.getElementById('mobile-menu-btn')?.addEventListener('click', () => {
                document.getElementById('mobile-menu')?.classList.toggle('hidden');
            });
            document.querySelectorAll('#mobile-menu a').forEach(a => {
                a.addEventListener('click', () => document.getElementById('mobile-menu')?.classList.add('hidden'));
            });

            // Scroll animations — Intersection Observer
            const io = new IntersectionObserver((entries) => {
                entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
            }, { threshold: .1, rootMargin: '0px 0px -60px 0px' });
            document.querySelectorAll('.fade-in-up').forEach(el => io.observe(el));
        })();
    </script>

    @yield('scripts')
</body>
</html>
