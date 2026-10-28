<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Crea (o actualiza) la cuenta administradora inicial del equipo interno.
 * No existe registro público: las cuentas se crean de forma administrada.
 *
 * Uso:   php artisan db:seed --class=AdminUserSeeder
 * Config (.env, opcional): ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD
 *
 * Es idempotente: si la cuenta ya existe, actualiza nombre y contraseña.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name     = env('ADMIN_NAME', 'Administrador NeuroManagement');
        $email    = strtolower(trim(env('ADMIN_EMAIL', 'admin@neuromanagement.cl')));
        $password = env('ADMIN_PASSWORD', 'NeuroAdmin2026!');

        $user = User::firstOrNew(['email' => $email]);

        $user->forceFill([
            'name'              => $name,
            'password'          => Hash::make($password),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        // La tabla puede tener la columna 'is_active' (default 0): la cuenta debe quedar activa.
        if (Schema::hasColumn('users', 'is_active')) {
            $user->forceFill(['is_active' => true]);
        }

        $user->save();

        $this->command?->info("Cuenta administradora lista: {$email}");

        if (! env('ADMIN_PASSWORD')) {
            $this->command?->warn('Se usó la contraseña por defecto. Define ADMIN_PASSWORD en .env para producción.');
        }
    }
}
