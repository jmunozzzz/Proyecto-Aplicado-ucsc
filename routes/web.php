<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonaController;

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
| Módulo: Personas / Alumnos
|--------------------------------------------------------------------------
*/
Route::prefix('personas')->name('personas.')->group(function () {
    Route::get('/',        [PersonaController::class, 'index'])->name('index');
    Route::get('/{persona}', [PersonaController::class, 'show'])->name('show');
});
