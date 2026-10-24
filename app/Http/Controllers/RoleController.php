<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;

class RoleController
{
    private function authorizeManagement(): void
    {
        abort_unless(auth('web')->user()?->hasRole('SuperAdmin', 'web'), 403);
    }

    
    public function index()
    {
        $this->authorizeManagement();

        return view('role.index', [
            'title' => 'Roles',
        ]);
    }
 
    public function create()
    {
        $this->authorizeManagement();

        return view('role.create', [
            'title' => 'Crear Rol',
        ]);
    }
  
    public function show(string $id)
    {
        $this->authorizeManagement();

        $role = Role::query()
            ->where('guard_name', 'web')
            ->with([
                'permissions' => fn ($query) => $query
                    ->where('guard_name', 'web')
                    ->orderBy('name'),
            ])
            ->findOrFail($id);

        abort_if($role->name === 'SuperAdmin', 403);

        return view('role.show', [
            'title' => 'Ver Rol',
            'role' => $role,
        ]);
    }
 
    public function edit(string $id)
    {
        $this->authorizeManagement();

        $role = Role::query()
            ->where('guard_name', 'web')
            ->findOrFail($id);

        abort_if($role->name === 'SuperAdmin', 403);

        return view('role.edit', [
            'title' => 'Editar Rol',
            'role' => $role,
        ]);
    }  
}