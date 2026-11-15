<?php

namespace App\Http\Controllers;

use App\Models\InscripcionSysteme;
use App\Models\Programa;
use App\Services\Systeme\ImportarAlumnosResult;
use App\Services\Systeme\InscripcionSyncResult;
use App\Services\Systeme\InscripcionSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class InscripcionController extends Controller
{
    /**
     * Listado de la bandeja de inscripciones sincronizadas desde Systeme.io
     * con filtros por curso, estado de acceso y estado de vinculación.
     */
    public function index(Request $request): View
    {
        Gate::authorize('manage-inscripciones');

        $programas = Schema::hasTable('programa')
            ? Programa::query()->orderBy('nombre')->get(['id', 'nombre', 'id_systeme_curso'])
            : collect();

        if (! Schema::hasTable('inscripcion_systeme')) {
            $inscripciones = new LengthAwarePaginator([], 0, 20, 1, [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]);
            $totales = [
                'total'          => 0,
                'acceso_activo'  => 0,
                'vinculadas'     => 0,
                'pendientes'     => 0,
            ];
            $ultimaSincronizacion = null;
            $migracionPendiente = true;

            return view('inscripciones.index', compact(
                'inscripciones',
                'programas',
                'totales',
                'ultimaSincronizacion',
                'migracionPendiente'
            ));
        }

        $migracionPendiente = false;

        $query = InscripcionSysteme::query()
            ->with(['programa', 'persona']);

        // Búsqueda libre por correo, IDs de Systeme.io, curso o nombre/apellido de persona vinculada
        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('email_contacto', 'like', "%{$search}%")
                    ->orWhere('id_systeme_inscripcion', 'like', "%{$search}%")
                    ->orWhere('id_systeme_contacto', 'like', "%{$search}%")
                    ->orWhere('id_systeme_curso', 'like', "%{$search}%")
                    ->orWhere('curso_nombre_systeme', 'like', "%{$search}%")
                    ->orWhereHas('persona', function ($pq) use ($search) {
                        $pq->where('nombres', 'like', "%{$search}%")
                            ->orWhere('apellidos', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('programa', function ($prq) use ($search) {
                        $prq->where('nombre', 'like', "%{$search}%");
                    });
            });
        }

        // Filtro por curso (acepta ID local de programa, id_systeme_curso o 'sin_curso')
        $curso = trim((string) ($request->input('curso') ?? $request->input('programa_id') ?? ''));
        if ($curso !== '') {
            if ($curso === 'sin_curso') {
                $query->whereNull('programa_id');
            } else {
                $query->where(function ($q) use ($curso) {
                    $q->where('id_systeme_curso', $curso);
                    if (ctype_digit($curso)) {
                        $q->orWhere('programa_id', (int) $curso);
                    }
                });
            }
        }

        // Filtro por estado de acceso en Systeme.io
        $acceso = trim((string) ($request->input('acceso') ?? $request->input('estado_acceso') ?? ''));
        if ($acceso === 'activo') {
            $query->where('systeme_activo', true);
        } elseif ($acceso === 'inactivo') {
            $query->where('systeme_activo', false);
        }

        // Filtro por estado de vinculación
        $vinculacion = trim((string) ($request->input('vinculacion') ?? $request->input('estado_vinculacion') ?? ''));
        if ($vinculacion === 'vinculada') {
            $query->where('vinculacion_estado', InscripcionSysteme::VINCULACION_VINCULADA);
        } elseif ($vinculacion === 'pendiente') {
            $query->whereIn('vinculacion_estado', [
                InscripcionSysteme::VINCULACION_PENDIENTE,
                InscripcionSysteme::VINCULACION_CONFLICTO,
            ]);
        } elseif ($vinculacion === 'sin_persona') {
            $query->whereIn('vinculacion_motivo_codigo', [
                InscripcionSysteme::MOTIVO_PERSONA_NO_ENCONTRADA,
                InscripcionSysteme::MOTIVO_DATOS_INCOMPLETOS,
            ]);
        } elseif ($vinculacion === 'conflicto') {
            $query->where('vinculacion_estado', InscripcionSysteme::VINCULACION_CONFLICTO);
        } elseif ($vinculacion === 'sin_curso') {
            $query->whereNull('programa_id');
        }

        // Ordenamiento
        $sortBy = (string) $request->input('sort', 'id_systeme_inscripcion');
        $sortDir = $request->input('dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = [
            'id_systeme_inscripcion',
            'email_contacto',
            'systeme_activo',
            'vinculacion_estado',
            'contacto_registrado_en',
            'sincronizado_en',
        ];
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id_systeme_inscripcion';
        }
        $query->orderBy($sortBy, $sortDir)->orderByDesc('id');

        $inscripciones = $query->paginate(20)->withQueryString();

        $totales = [
            'total'         => InscripcionSysteme::count(),
            'acceso_activo' => InscripcionSysteme::where('systeme_activo', true)->count(),
            'vinculadas'    => InscripcionSysteme::where('vinculacion_estado', InscripcionSysteme::VINCULACION_VINCULADA)->count(),
            'pendientes'    => InscripcionSysteme::whereIn('vinculacion_estado', [
                InscripcionSysteme::VINCULACION_PENDIENTE,
                InscripcionSysteme::VINCULACION_CONFLICTO,
            ])->count(),
        ];

        $ultimaSincronizacion = InscripcionSysteme::max('sincronizado_en');

        return view('inscripciones.index', compact(
            'inscripciones',
            'programas',
            'totales',
            'ultimaSincronizacion',
            'migracionPendiente'
        ));
    }

    /**
     * Ejecuta la sincronización unidireccional de inscripciones desde Systeme.io → bandeja local.
     */
    public function sync(InscripcionSyncService $syncService): RedirectResponse
    {
        Gate::authorize('manage-inscripciones');

        if (! Schema::hasTable('inscripcion_systeme')) {
            $resultado = new InscripcionSyncResult();
            $resultado->listadoCompleto = false;
            $resultado->errorGeneral = 'La tabla inscripcion_systeme aún no existe. Ejecuta php artisan migrate antes de sincronizar.';

            return redirect()
                ->route('inscripciones.index')
                ->with('sync_result', $resultado->toArray());
        }

        $resultado = $syncService->sincronizar();

        return redirect()
            ->route('inscripciones.index')
            ->with('sync_result', $resultado->toArray());
    }

    /**
     * Procesa las inscripciones pendientes del curso seleccionado (por defecto
     * Evolución Consciente, ID #674252) consultando `GET /api/contacts/{id}`
     * para crear o vincular fichas `Persona`.
     */
    public function importAlumnos(Request $request, InscripcionSyncService $syncService): RedirectResponse
    {
        Gate::authorize('manage-inscripciones');

        $curso = trim((string) ($request->input('curso') ?? $request->input('programa_id') ?? ''));
        if ($curso === '') {
            $curso = InscripcionSyncService::CURSO_DEFAULT_SYSTEME_ID;
        }

        if (! Schema::hasTable('inscripcion_systeme') || ! Schema::hasTable('persona')) {
            $resultado = new ImportarAlumnosResult();
            $resultado->cursoFiltro = $curso;
            $resultado->errorGeneral = 'Las tablas requeridas (inscripcion_systeme / persona) no están disponibles.';

            return redirect()
                ->route('inscripciones.index')
                ->with('import_result', $resultado->toArray());
        }

        $resultado = $syncService->importarAlumnosPendientes($curso);

        $redirectParams = $request->filled('curso') ? ['curso' => $curso] : [];

        return redirect()
            ->route('inscripciones.index', $redirectParams)
            ->with('import_result', $resultado->toArray());
    }
}

