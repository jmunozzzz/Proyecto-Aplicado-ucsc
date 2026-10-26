<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;

class PersonaController extends Controller
{
    /**
     * Listado de personas/alumnos con búsqueda, filtros y paginación.
     */
    public function index(Request $request)
    {
        $query = Persona::query();

        // Búsqueda por nombre, apellido, email o documento
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombres',          'like', "%{$search}%")
                  ->orWhere('apellidos',       'like', "%{$search}%")
                  ->orWhere('email',           'like', "%{$search}%")
                  ->orWhere('numero_documento','like', "%{$search}%");
            });
        }

        // Filtro por estado (lead, activo, inactivo…)
        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        // Filtro por país
        if ($pais = $request->input('pais')) {
            $query->where('pais', $pais);
        }

        // Filtro por género
        if ($genero = $request->input('genero')) {
            $query->where('genero', $genero);
        }

        // Ordenamiento
        $sortBy  = $request->input('sort', 'creado_en');
        $sortDir = $request->input('dir', 'desc');
        $allowedSorts = ['nombres', 'apellidos', 'email', 'estado', 'creado_en'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'creado_en';
        }
        $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');

        // Paginación
        $personas = $query->paginate(20)->withQueryString();

        // Datos para filtros dropdown
        $estados  = Persona::select('estado')->distinct()->whereNotNull('estado')->pluck('estado');
        $paises   = Persona::select('pais')->distinct()->whereNotNull('pais')->orderBy('pais')->pluck('pais');
        $generos  = Persona::select('genero')->distinct()->whereNotNull('genero')->pluck('genero');

        // Totales rápidos para los badges del header
        $totales = [
            'total'    => Persona::count(),
            'activos'  => Persona::where('estado', 'activo')->count(),
            'leads'    => Persona::where('estado', 'lead')->count(),
            'inactivos'=> Persona::where('estado', 'inactivo')->count(),
        ];

        return view('personas.index', compact(
            'personas', 'estados', 'paises', 'generos', 'totales'
        ));
    }

    /**
     * Detalle de una persona con sus inscripciones y datos laborales.
     */
    public function show(Persona $persona)
    {
        $persona->load([
            'inscripciones.cohorte.programa',
            'inscripciones.asistencia',
            'historialLaboral.empresa',
            'usuario.roles',
        ]);

        return view('personas.show', compact('persona'));
    }
}
