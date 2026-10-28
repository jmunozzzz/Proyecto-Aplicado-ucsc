<?php

/*
|--------------------------------------------------------------------------
| Navegación del área administrativa
|--------------------------------------------------------------------------
| - 'route'  : nombre de la ruta real. Si es null o la ruta no existe, el
|              módulo se muestra deshabilitado con la etiqueta "Próximamente".
| - 'active' : patrón(es) de routeIs() que marcan el módulo como activo
|              (incluye páginas de detalle/edición, p. ej. 'personas.*').
| - 'icon'   : nombre de icono del componente <x-icon> (Heroicons outline).
|
| Aún no hay permisos implementados para el panel: cuando existan, agregar
| una clave 'can' por ítem y filtrarla en App\Support\AdminNavigation.
*/

return [

    'navigation' => [
        [
            'title' => 'Principal',
            'items' => [
                ['label' => 'Inicio', 'icon' => 'home', 'route' => 'dashboard', 'active' => 'dashboard',
                 'description' => 'Resumen y accesos rápidos del panel.'],
            ],
        ],
        [
            'title' => 'Gestión académica',
            'items' => [
                ['label' => 'Personas / Alumnos', 'icon' => 'users', 'route' => 'personas.index', 'active' => 'personas.*',
                 'description' => 'Directorio de contactos, leads y participantes.'],
                ['label' => 'Empresas',           'icon' => 'building-office', 'route' => null],
                ['label' => 'Programas / Cursos', 'icon' => 'academic-cap',    'route' => null],
                ['label' => 'Cohortes',           'icon' => 'user-group',      'route' => null],
                ['label' => 'Inscripciones',      'icon' => 'clipboard-list',  'route' => null],
                ['label' => 'Gestión de accesos', 'icon' => 'key',             'route' => null],
            ],
        ],
        [
            'title' => 'Finanzas',
            'items' => [
                ['label' => 'Ventas e ingresos', 'icon' => 'chart-bar',   'route' => null],
                ['label' => 'Pagos',             'icon' => 'credit-card', 'route' => null],
            ],
        ],
        [
            'title' => 'Administración',
            'items' => [
                ['label' => 'Equipo y permisos',      'icon' => 'shield-check', 'route' => null],
                ['label' => 'Historial de actividad', 'icon' => 'clock',        'route' => null],
                ['label' => 'Configuración',          'icon' => 'cog',          'route' => null],
            ],
        ],
    ],

];
