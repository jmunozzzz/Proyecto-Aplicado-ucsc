<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonaController;
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
});

