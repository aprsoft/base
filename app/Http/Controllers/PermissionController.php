<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController
{
    public function index()
    {
        return view('permission.index', [
            'title' => 'Permisos',
        ]);
    }
 
    public function create()
    {
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
        $permission = Permission::query()
            ->where('guard_name', 'web')
            ->with([
                'roles' => fn ($query) => $query->orderBy('name'),
            ])
            ->findOrFail($id);

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

        return view('permission.edit', [
            'title' => 'Editar Permiso',
            'permission' => $permission,
        ]);
    }
}