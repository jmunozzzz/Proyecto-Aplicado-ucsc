{{--
    Breadcrumbs del panel.
    Uso: <x-admin.breadcrumbs :items="[['label' => 'Inicio', 'url' => route('dashboard')], ['label' => 'Personas / Alumnos']]" />
    El último ítem se muestra como página actual (sin enlace).
--}}
@props(['items' => []])

<nav aria-label="Ruta de navegación" class="mb-5">
    <ol class="flex flex-wrap items-center gap-1.5 text-sm">
        @foreach ($items as $item)
            <li class="flex items-center gap-1.5 min-w-0">
                @if (! $loop->first)
                    <x-icon name="chevron-right" class="w-3.5 h-3.5 text-slate-600" />
                @endif

                @if (! $loop->last && ! empty($item['url']))
                    <a href="{{ $item['url'] }}"
                       class="admin-focus inline-flex items-center gap-1.5 text-slate-400 hover:text-nm-cyan transition-colors rounded">
                        @if ($loop->first)<x-icon name="home" class="w-4 h-4" />@endif
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="text-slate-200 font-medium truncate" @if ($loop->last) aria-current="page" @endif>{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
