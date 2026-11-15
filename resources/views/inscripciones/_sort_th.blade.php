@php
    $currentSort = request('sort', 'id_systeme_inscripcion');
    $currentDir  = request('dir',  'desc');
    $newDir      = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
    $isActive    = $currentSort === $col;
@endphp
<a href="{{ route('inscripciones.index', array_merge(request()->except(['sort','dir','page']), ['sort'=>$col,'dir'=>$newDir])) }}"
   class="{{ $isActive ? ($currentDir==='asc' ? 'sort-asc text-nm-cyan' : 'sort-desc text-nm-cyan') : '' }}">
    {{ $label }}
</a>

