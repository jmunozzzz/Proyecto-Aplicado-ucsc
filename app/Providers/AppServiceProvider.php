<?php

namespace App\Providers;

use App\Models\User;
use App\Models\UsuarioRol;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Lectura del directorio de Personas / Alumnos (contiene datos personales):
        // solo administradores activos.
        Gate::define('view-personas', function (User $user): bool {
            return $user->tieneAlgunRol([UsuarioRol::ROL_ADMINISTRADOR]);
        });

        // Lectura del catálogo de Programas / Cursos (listado y detalle):
        // administradores y facilitadores activos. Sincronizar y editar siguen en 'manage-programas'.
        Gate::define('view-programas', function (User $user): bool {
            return $user->tieneAlgunRol([UsuarioRol::ROL_ADMINISTRADOR, UsuarioRol::ROL_FACILITADOR]);
        });

        // Autorización administrativa para sincronizar y editar programas/cursos:
        // requiere que el usuario esté activo y posea el rol 'administrador_nm'.
        Gate::define('manage-programas', function (User $user): bool {
            return $user->esAdministradorActivo();
        });

        // Autorización administrativa para consultar y sincronizar la bandeja de inscripciones:
        // requiere que el usuario esté activo y posea el rol 'administrador_nm'.
        Gate::define('manage-inscripciones', function (User $user): bool {
            return $user->esAdministradorActivo();
        });
    }
}
