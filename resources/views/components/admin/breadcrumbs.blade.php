{{--
    Ruta de navegación del panel, mostrada en la barra superior.
    Uso: <x-admin.breadcrumbs :items="[['label' => 'Inicio', 'url' => route('dashboard')], ['label' => 'Personas / Alumnos']]" />

    - La página actual (último ítem) no se muestra: el título del contenido ya la nombra.
    - Si la ruta tiene un solo ítem (p. ej. el Inicio), se muestra el page_title de la vista
      como título principal de la pantalla.
--}}
@props(['items' => []])

@php
    $items   = array_values($items);
    $parents = array_slice($items, 0, -1);
    $actual  = end($items) ?: null;
@endphp

@if (count($items) <= 1)
    <h1 class="text-base sm:text-lg font-semibold text-white truncate">
        {{ trim($__env->yieldContent('page_title')) ?: ($actual['label'] ?? 'Panel') }}
    </h1>
@else
    <nav aria-label="Ruta de navegación" class="min-w-0">
        <ol class="flex items-center gap-1.5 text-sm min-w-0">
            @foreach ($parents as $item)
                <li class="flex items-center gap-1.5 min-w-0">
                    @unless ($loop->first)
                        <x-icon name="chevron-right" class="w-3.5 h-3.5 shrink-0 text-slate-600" />
                    @endunless

                    @if (! empty($item['url']))
                        <a href="{{ $item['url'] }}"
                           class="admin-focus truncate rounded text-slate-400 hover:text-white transition-colors">{{ $item['label'] }}</a>
                    @else
                        <span class="truncate text-slate-400">{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
        @if ($actual)
            <span class="sr-only">Página actual: {{ $actual['label'] }}</span>
        @endif
    </nav>
@endif
