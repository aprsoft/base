<?php

namespace App\Support\Permissions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

final class PermissionCatalog
{
    /**
     * Agrupa únicamente los permisos que el componente ya autorizó.
     * Detecta los módulos a partir del prefijo anterior al primer punto.
     */
    public function groups(Collection $permissions): Collection
    {
        return $permissions
            ->groupBy(static function (Permission $permission): string {
                $dot = strpos($permission->name, '.');

                return $dot === false || $dot === 0
                    ? 'otros'
                    : substr($permission->name, 0, $dot);
            })
            ->sortKeys()
            ->map(function (Collection $items, $module): array {
                $module = (string) $module;

                return [
                    'module' => $module,
                    'label' => $this->label($module),
                    'permissions' => $items->values(),
                    'ids' => $items->pluck('id')->map(static fn ($id): int => (int) $id)->all(),
                    'names' => $items->pluck('name')->values()->all(),
                ];
            })
            ->values();
    }

    /**
     * Envía al navegador únicamente los datos necesarios para la interfaz.
     */
    public function forAlpine(Collection $groups): array
    {
        return $groups
            ->map(static fn (array $group): array => [
                'module' => $group['module'],
                'label' => $group['label'],
                'ids' => $group['ids'],
                'names' => $group['names'],
            ])
            ->all();
    }

    private function label(string $module): string
    {
        $key = 'permission_modules.'.$module;
        $label = Lang::get($key);

        // La interfaz actual de BASE está en español, aunque APP_LOCALE sea en.
        if ($label === $key && app()->getLocale() !== 'es') {
            $label = Lang::get($key, [], 'es');
        }

        return $label === $key ? Str::headline($module) : $label;
    }
}
