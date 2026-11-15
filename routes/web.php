<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Acceso al sistema administrativo (sin registro público)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.attempt');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Web Routes — Neuro Management Landing Page
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', function () {
    return view('landing.index');
})->name('home');

// Formulario de Contacto
Route::post('/contacto', function (Request $request) {
    $validated = $request->validate([
        'nombre'  => 'required|string|max:255',
        'email'   => 'required|email|max:255',
        'empresa' => 'required|string|max:255',
        'cargo'   => 'required|string|max:255',
        'mensaje' => 'required|string|min:20|max:2000',
    ], [
        'nombre.required'  => 'El nombre es obligatorio.',
        'email.required'   => 'El correo electrónico es obligatorio.',
        'email.email'      => 'Ingresa un correo electrónico válido.',
        'empresa.required' => 'El nombre de tu empresa es obligatorio.',
        'cargo.required'   => 'Tu cargo es obligatorio.',
        'mensaje.required' => 'El mensaje es obligatorio.',
        'mensaje.min'      => 'El mensaje debe tener al menos 20 caracteres.',
    ]);

    // TODO: Integrar envío de email (Mail::to / Mailchimp / HubSpot)
    // Mail::to('jorge@neuromanagement.com')->send(new ContactoMail($validated));

    return back()
        ->with('success', '¡Mensaje enviado con éxito! Jorge se pondrá en contacto contigo pronto.')
        ->with('nombre', $validated['nombre']);
})->name('contacto.send');

/*
|--------------------------------------------------------------------------
| Área Administrativa (Requiere autenticación)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Módulo: Personas / Alumnos
    Route::prefix('personas')->name('personas.')->group(function () {
        Route::get('/',          [PersonaController::class, 'index'])->name('index');
        Route::get('/{persona}', [PersonaController::class, 'show'])->name('show');
    });

    // Módulo: Programas / Cursos
    Route::prefix('programas')->name('programas.')->group(function () {
        Route::get('/',           [ProgramaController::class, 'index'])->name('index');
        Route::post('/sincronizar', [ProgramaController::class, 'sync'])
            ->middleware('can:manage-programas')
            ->name('sync');
        Route::get('/{programa}', [ProgramaController::class, 'show'])->name('show');
        Route::patch('/{programa}', [ProgramaController::class, 'update'])
            ->middleware('can:manage-programas')
            ->name('update');
    });

    // Módulo: Inscripciones (Bandeja de inscripciones de Systeme.io)
    Route::prefix('inscripciones')
        ->name('inscripciones.')
        ->middleware('can:manage-inscripciones')
        ->group(function () {
            Route::get('/', [InscripcionController::class, 'index'])->name('index');
            Route::post('/sincronizar', [InscripcionController::class, 'sync'])->name('sync');
            Route::post('/importar-alumnos', [InscripcionController::class, 'importAlumnos'])->name('import-alumnos');
        });
});

// Prueba de conexión con ngrok
Route::get('/prueba-systeme', function () {
    return response()->json([
        'ok' => true,
        'mensaje' => 'Ngrok conectado con Laravel',
    ]);
});

// Recibe inscripciones y crea o actualiza la persona.
Route::post('/webhooks/systeme', function (Request $request) {
    // Verificar la clave antes de procesar datos.
    $claveEsperada = (string) config('services.systeme.webhook_token');
    $claveRecibida = (string) $request->query('token', '');

    abort_unless(
        $claveEsperada !== ''
            && hash_equals($claveEsperada, $claveRecibida),
        403,
        'Webhook no autorizado.'
    );

    // Ignorar otros eventos o cursos.
    if (
        $request->input('type') !== 'contact.course.enrolled'
        || (string) $request->input('data.course.id') !== '674252'
    ) {
        return response()->json(['ignorado' => true]);
    }

    $datos = $request->validate([
        'data.contact.id' => ['required', 'integer', 'min:1'],
        'data.contact.email' => ['required', 'email', 'max:255'],
        'data.contact.fields.first_name' => [
            'required', 'string', 'max:255',
        ],
        'data.contact.fields.surname' => [
            'required', 'string', 'max:255',
        ],
    ]);

    $contacto = $datos['data']['contact'];
    $contactoId = (string) $contacto['id'];
    $email = strtolower(trim($contacto['email']));
    $nombres = trim($contacto['fields']['first_name']);
    $apellidos = trim($contacto['fields']['surname']);

    abort_if(
        $nombres === '' || $apellidos === '',
        422,
        'El nombre y el apellido son obligatorios.'
    );

    $persona = \Illuminate\Support\Facades\DB::transaction(
        function () use ($contactoId, $email, $nombres, $apellidos) {
            $persona = \App\Models\Persona::where(
                'id_systeme_contacto',
                $contactoId
            )->lockForUpdate()->first();

            // Si no está vinculada, buscar coincidencias por correo.
            if (!$persona) {
                $coincidencias = \App\Models\Persona::whereRaw(
                    'LOWER(TRIM(email)) = ?',
                    [$email]
                )->lockForUpdate()->limit(2)->get();

                abort_if(
                    $coincidencias->count() > 1,
                    409,
                    'Hay varias personas con ese correo; revisar vinculación.'
                );

                $persona = $coincidencias->first();

                abort_if(
                    $persona
                        && $persona->id_systeme_contacto !== null
                        && (string) $persona->id_systeme_contacto !== $contactoId,
                    409,
                    'El correo ya está vinculado a otro contacto de Systeme.io.'
                );
            }

            if (!$persona) {
                // La clave única del contacto evita duplicar su ID.
                $persona = \App\Models\Persona::createOrFirst(
                    ['id_systeme_contacto' => $contactoId],
                    [
                        'nombres' => $nombres,
                        'apellidos' => $apellidos,
                        'email' => $email,
                        'origen_lead' => 'systeme.io',
                        'estado' => 'alumno_activo',
                    ]
                );
            } else {
                $persona->id_systeme_contacto = $contactoId;
                $persona->nombres = $nombres;
                $persona->apellidos = $apellidos;
                $persona->email = $email;

                if ($persona->estado === 'lead') {
                    $persona->estado = 'alumno_activo';
                }

                $persona->save();
            }

            return $persona;
        }
    );

    \Illuminate\Support\Facades\Log::info(
        'Persona sincronizada desde Systeme.io',
        [
            'persona_id' => $persona->id,
            'contacto_id' => $contactoId,
        ]
    );

    return response()->json([
        'recibido' => true,
        'persona_id' => $persona->id,
    ]);
})->withoutMiddleware([
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
]);