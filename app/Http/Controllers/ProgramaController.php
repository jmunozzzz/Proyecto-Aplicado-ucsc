<?php

namespace App\Http\Controllers;

use App\Models\Programa;
use App\Services\Systeme\ProgramaSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProgramaController extends Controller
{
    /**
     * Listado de programas / cursos con búsqueda por nombre, filtro por estado y resumen de sincronización.
     */
    public function index(Request $request): View
    {
        $query = Programa::query()
            ->withCount([
                'modulos' => fn ($q) => $q->where(fn ($sub) => $sub->whereNull('systeme_presente')->orWhere('systeme_presente', true)),
                'lecciones' => fn ($q) => $q->where(fn ($sub) => $sub->whereNull('leccion.systeme_presente')->orWhere('leccion.systeme_presente', true)),
            ]);

        // Búsqueda por nombre (o categoría / instructor)
        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('categoria', 'like', "%{$search}%")
                    ->orWhere('instructor', 'like', "%{$search}%");
            });
        }

        // Filtro por estado en Systeme.io
        $estado = (string) $request->input('estado', '');
        if ($estado === 'activo') {
            $query->where('systeme_activo', true)
                ->where(fn ($q) => $q->whereNull('systeme_presente')->orWhere('systeme_presente', true));
        } elseif ($estado === 'inactivo') {
            $query->where('systeme_activo', false)
                ->where(fn ($q) => $q->whereNull('systeme_presente')->orWhere('systeme_presente', true));
        } elseif ($estado === 'no_presente') {
            $query->where('systeme_presente', false);
        } elseif ($estado === 'pendiente_estructura') {
            $query->whereIn('estructura_estado', [Programa::ESTRUCTURA_PENDIENTE, Programa::ESTRUCTURA_ERROR]);
        }

        // Filtro por tipo de programa local
        if ($linea = (string) $request->input('linea', '')) {
            if ($linea === 'pendiente') {
                $query->whereNull('linea');
            } elseif (array_key_exists($linea, Programa::LINEAS)) {
                $query->where('linea', $linea);
            }
        }

        // Ordenamiento
        $sortBy = (string) $request->input('sort', 'nombre');
        $sortDir = $request->input('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['nombre', 'systeme_activo', 'sincronizado_en', 'horas', 'linea'];
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'nombre';
        }
        $query->orderBy($sortBy, $sortDir)->orderBy('id', 'asc');

        $programas = $query->paginate(20)->withQueryString();

        // Totales rápidos para las tarjetas superiores
        $totales = [
            'total'                => Programa::count(),
            'activos_systeme'      => Programa::where('systeme_activo', true)
                ->where(fn ($q) => $q->whereNull('systeme_presente')->orWhere('systeme_presente', true))
                ->count(),
            'inactivos_systeme'    => Programa::where('systeme_activo', false)
                ->where(fn ($q) => $q->whereNull('systeme_presente')->orWhere('systeme_presente', true))
                ->count(),
            'estructuras_pendientes' => Programa::whereIn('estructura_estado', [
                Programa::ESTRUCTURA_PENDIENTE,
                Programa::ESTRUCTURA_ERROR,
            ])->count(),
        ];

        $ultimaSincronizacion = Programa::max('sincronizado_en');

        return view('programas.index', compact(
            'programas',
            'totales',
            'ultimaSincronizacion'
        ));
    }

    /**
     * Detalle de un programa con información de Systeme.io, formulario de datos locales
     * y módulos desplegables con lecciones ordenadas por position ascendente.
     */
    public function show(Programa $programa): View
    {
        $programa->load([
            'modulos' => fn ($q) => $q->orderBy('orden'),
            'modulos.lecciones' => fn ($q) => $q->orderBy('orden'),
        ]);

        $modulosPresentes = $programa->modulos->filter(
            fn ($m) => $m->systeme_presente === null || $m->systeme_presente === true
        );
        $modulosHistoricos = $programa->modulos->filter(
            fn ($m) => $m->systeme_presente === false
        );

        $totalLeccionesPresentes = $modulosPresentes->sum(
            fn ($m) => $m->lecciones->filter(fn ($l) => $l->systeme_presente === null || $l->systeme_presente === true)->count()
        );
        $leccionesActivas = $modulosPresentes->sum(
            fn ($m) => $m->lecciones->filter(fn ($l) => ($l->systeme_presente === null || $l->systeme_presente === true) && $l->systeme_activo === true)->count()
        );

        return view('programas.show', compact(
            'programa',
            'modulosPresentes',
            'modulosHistoricos',
            'totalLeccionesPresentes',
            'leccionesActivas'
        ));
    }

    /**
     * Actualiza únicamente la información académica local del programa en Laravel.
     * No modifica ningún dato en Systeme.io.
     */
    public function update(Request $request, Programa $programa): RedirectResponse
    {
        Gate::authorize('manage-programas');

        // Normalizar cadenas vacías a null para permitir información pendiente.
        $request->merge([
            'linea'      => $request->filled('linea') ? trim((string) $request->input('linea')) : null,
            'modalidad'  => $request->filled('modalidad') ? trim((string) $request->input('modalidad')) : null,
            'categoria'  => $request->filled('categoria') ? trim((string) $request->input('categoria')) : null,
            'horas'      => $request->filled('horas') ? $request->input('horas') : null,
            'objetivos'  => $request->filled('objetivos') ? trim((string) $request->input('objetivos')) : null,
            'requisitos' => $request->filled('requisitos') ? trim((string) $request->input('requisitos')) : null,
        ]);

        $validated = $request->validate([
            'linea'      => ['nullable', 'string', Rule::in(array_keys(Programa::LINEAS))],
            'modalidad'  => ['nullable', 'string', Rule::in(array_keys(Programa::MODALIDADES))],
            'categoria'  => ['nullable', 'string', 'max:255'],
            'horas'      => ['nullable', 'numeric', 'gt:0', 'max:999999.99'],
            'objetivos'  => ['nullable', 'string', 'max:10000'],
            'requisitos' => ['nullable', 'string', 'max:10000'],
        ], [
            'linea.in'         => 'Selecciona un tipo de programa válido (abierto o corporativo).',
            'modalidad.in'     => 'Selecciona una modalidad válida (sincrónica, asincrónica o mixta).',
            'categoria.max'    => 'La categoría no puede superar los 255 caracteres.',
            'horas.numeric'    => 'La duración en horas debe ser un valor numérico.',
            'horas.gt'         => 'La duración en horas debe ser mayor a 0.',
            'horas.max'        => 'La duración en horas excede el máximo permitido.',
            'objetivos.max'    => 'Los objetivos no pueden superar los 10.000 caracteres.',
            'requisitos.max'   => 'Los requisitos no pueden superar los 10.000 caracteres.',
        ]);

        $programa->fill($validated);
        $programa->save();

        return redirect()
            ->route('programas.show', $programa)
            ->with('status', 'Información local del programa guardada correctamente.');
    }

    /**
     * Ejecuta la sincronización unidireccional desde Systeme.io → Laravel.
     */
    public function sync(ProgramaSyncService $syncService): RedirectResponse
    {
        Gate::authorize('manage-programas');

        $resultado = $syncService->sincronizar();

        return redirect()
            ->route('programas.index')
            ->with('sync_result', $resultado->toArray());
    }
}

