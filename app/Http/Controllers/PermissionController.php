<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Gate;

class PermissionController
{

    public function index()
    {
        Gate::authorize('viewAny', Permission::class);

        return view('permission.index', [
            'title' => 'Permisos',
        ]);
    }
 
    public function create()
    {
        Gate::authorize('create', Permission::class);

        return view('permission.create', [
            'title' => 'Crear Permiso',
        ]);
    }

    public function store(Request $request)
    {
        abort(405);
    }

    public function show(string $id)
    {
        $permission = Permission::query()
            ->where('guard_name', 'web')
            ->with([
                'roles' => fn ($query) => $query
                    ->where('guard_name', 'web')
                    ->orderBy('name'),
            ])
            ->findOrFail($id);

        Gate::authorize('view', $permission);

        return view('permission.show', [
            'title' => 'Ver Permiso',
            'permission' => $permission,
        ]);
    }

    public function edit(string $id)
    {
        $permission = Permission::query()
            ->where('guard_name', 'web')
            ->findOrFail($id);

        Gate::authorize('update', $permission);

        return view('permission.edit', [
            'title' => 'Editar Permiso',
            'permission' => $permission,
        ]);
    }
}