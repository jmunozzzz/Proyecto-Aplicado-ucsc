<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Resuelve la navegación del panel (config/admin.php) para la petición actual:
 * disponibilidad real de cada ruta, URL y estado activo.
 */
class AdminNavigation
{
    /**
     * @return array<int, array{title: string, items: array<int, array<string, mixed>>}>
     */
    public static function sections(): array
    {
        $user = auth()->user();

        return collect(config('admin.navigation', []))
            ->map(function (array $section) use ($user) {
                $items = collect($section['items'])
                    ->filter(fn (array $item) => ! isset($item['can']) || ($user !== null && $user->can($item['can'])))
                    ->map(fn (array $item) => self::resolve($item))
                    ->values()
                    ->all();

                return [
                    'title' => $section['title'],
                    'items' => $items,
                ];
            })
            ->filter(fn (array $section) => ! empty($section['items']))
            ->values()
            ->all();
    }

    /** Ítems con ruta implementada (útil para accesos del Dashboard). */
    public static function available(): array
    {
        return collect(self::sections())
            ->flatMap(fn ($s) => $s['items'])
            ->where('available', true)
            ->values()
            ->all();
    }

    /** Ítems aún no implementados. */
    public static function upcoming(): array
    {
        return collect(self::sections())
            ->flatMap(fn ($s) => $s['items'])
            ->where('available', false)
            ->values()
            ->all();
    }

    private static function resolve(array $item): array
    {
        $route     = $item['route'] ?? null;
        $available = $route !== null && Route::has($route);

        return $item + [
            'available'   => $available,
            'url'         => $available ? route($route) : null,
            'is_active'   => $available && request()->routeIs(...(array) ($item['active'] ?? $route)),
            'description' => null,
        ];
    }
}
