<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;

class RoleController
{
    
    public function index()
    {
        return view('role.index', [
            'title' => 'Roles',
        ]);
    }
 
    public function create()
    {
        return view('role.create', [
            'title' => 'Crear Rol',
        ]);
    }
  
    public function show(string $id)
    {
        $role = Role::query()
            ->where('guard_name', 'web')
            ->with([
                'permissions' => fn ($query) => $query
                    ->where('guard_name', 'web')
                    ->orderBy('name'),
            ])
            ->findOrFail($id);

        return view('role.show', [
            'title' => 'Ver Rol',
            'role' => $role,
        ]);
    }
 
    public function edit(string $id)
    {
        $role = Role::query()
            ->where('guard_name', 'web')
            ->findOrFail($id);

        return view('role.edit', [
            'title' => 'Editar Rol',
            'role' => $role,
        ]);
    }  
}