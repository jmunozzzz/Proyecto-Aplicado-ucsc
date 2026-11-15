<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Obtiene la cuenta de dominio (tabla `usuario`) vinculada de forma inequívoca
     * a este User. Si las coincidencias por sujeto_auth, ID y correo apuntan a
     * registros distintos o contradictorios, devuelve null para denegar el permiso.
     */
    public function usuarioDominio(): ?Usuario
    {
        if (! Schema::hasTable('usuario')) {
            return null;
        }

        $email = strtolower(trim((string) $this->email));
        $idStr = $this->getKey() !== null ? (string) $this->getKey() : null;

        if ($idStr === null && $email === '') {
            return null;
        }

        $tieneTablaPersona = Schema::hasTable('persona');
        $personaIdPorEmail = null;

        if ($tieneTablaPersona && $email !== '') {
            $personaIds = Persona::query()
                ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
                ->limit(2)
                ->pluck('id');

            // Si el correo coincide con más de una persona, la vinculación es ambigua.
            if ($personaIds->count() > 1) {
                return null;
            }

            $personaIdPorEmail = $personaIds->first();
        }

        $usuarios = Usuario::query()
            ->where(function ($q) use ($idStr, $email, $personaIdPorEmail) {
                if ($idStr !== null) {
                    $q->whereRaw('TRIM(sujeto_auth) = ?', [$idStr]);
                }
                if ($email !== '') {
                    $q->orWhereRaw('LOWER(TRIM(sujeto_auth)) = ?', [$email]);
                }
                if ($personaIdPorEmail !== null) {
                    $q->orWhere('persona_id', $personaIdPorEmail);
                }
            })
            ->limit(2)
            ->get();

        // Debe existir exactamente un registro Usuario; si hay 0 o más de 1, se deniega.
        if ($usuarios->count() !== 1) {
            return null;
        }

        /** @var Usuario $usuario */
        $usuario = $usuarios->first();

        // Debe corresponder al proveedor de autenticación local de Laravel.
        if (strtolower(trim((string) $usuario->proveedor_auth)) !== Usuario::PROVEEDOR_LOCAL) {
            return null;
        }

        // El sujeto_auth del registro debe corresponder al ID o al correo de este User.
        $sujeto = strtolower(trim((string) $usuario->sujeto_auth));
        $coincideConId = $idStr !== null && $sujeto === strtolower($idStr);
        $coincideConEmail = $email !== '' && $sujeto === $email;

        if (! $coincideConId && ! $coincideConEmail) {
            return null;
        }

        // Si el Usuario está vinculado a una Persona, debe ser la misma Persona del correo de este User.
        if ($usuario->persona_id !== null) {
            if ($personaIdPorEmail !== null && (int) $usuario->persona_id !== (int) $personaIdPorEmail) {
                return null;
            }

            if ($tieneTablaPersona) {
                $emailPersona = Persona::query()
                    ->whereKey($usuario->persona_id)
                    ->value('email');

                if ($emailPersona === null || strtolower(trim((string) $emailPersona)) !== $email) {
                    return null;
                }
            }
        } elseif ($personaIdPorEmail !== null) {
            // Existe una Persona con este correo pero el registro Usuario no está vinculado a ella.
            return null;
        }

        return $usuario;
    }

    /**
     * Comprueba si el usuario está activo y posee el rol indicado en `usuario_rol`.
     */
    public function tieneRol(string $rol): bool
    {
        if (! $this->estaActivo()) {
            return false;
        }

        $usuario = $this->usuarioDominio();
        if ($usuario === null) {
            return false;
        }

        return $usuario->tieneRol($rol);
    }

    /**
     * Comprueba si el usuario posee al menos uno de los roles indicados.
     * Delega en tieneRol() para aplicar las mismas reglas (cuenta activa,
     * usuario de dominio activo y rol global sin empresa asociada).
     *
     * @param  list<string>  $roles
     */
    public function tieneAlgunRol(array $roles): bool
    {
        foreach ($roles as $rol) {
            if ($this->tieneRol($rol)) {
                return true;
            }
        }

        return false;
    }

    public function esAdministradorActivo(): bool
    {
        return $this->tieneRol(UsuarioRol::ROL_ADMINISTRADOR);
    }

    public function estaActivo(): bool
    {
        $attributes = $this->getAttributes();

        return array_key_exists('is_active', $attributes)
            ? (bool) $attributes['is_active']
            : true;
    }
}
