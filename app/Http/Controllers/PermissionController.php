<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController
{
    private function authorizeManagement(): void
    {
        abort_unless(auth('web')->user()?->hasRole('SuperAdmin', 'web'), 403);
    }

    public function index()
    {
        $this->authorizeManagement();

        return view('permission.index', [
            'title' => 'Permisos',
        ]);
    }
 
    public function create()
    {
        $this->authorizeManagement();

        return view('permission.create', [
            'title' => 'Crear Permiso',
        ]);
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        $this->authorizeManagement();

        $permission = Permission::query()
            ->where('guard_name', 'web')
            ->with([
                'roles' => fn ($query) => $query
                    ->where('guard_name', 'web')
                    ->orderBy('name'),
            ])
            ->findOrFail($id);

        return view('permission.show', [
            'title' => 'Ver Permiso',
            'permission' => $permission,
        ]);
    }

    public function edit(string $id)
    {
        $this->authorizeManagement();

        $permission = Permission::query()
            ->where('guard_name', 'web')
            ->findOrFail($id);

        return view('permission.edit', [
            'title' => 'Editar Permiso',
            'permission' => $permission,
        ]);
    }
}