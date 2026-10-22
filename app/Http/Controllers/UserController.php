<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;

class UserController extends Controller
{
  
    public function index()
    {        

        return view('users.index', ['title'=> 'Usuarios']);
    }

    public function show(User $user)
    {
        
        return view('users.show',['title'=>'Mostrar Usuario','user'=> $user]);
    }

    public function create()
    {
        return view('users.create',['title'=>'Crear Usuario']);
    }

   
    public function edit(User $user)
    {
        abort_unless(auth('web')->user()?->can('users.edit'), 403);
        abort_if((int) $user->id === 1, 403);
        abort_if($user->trashed(), 404);

        if ($user->hasRole('SuperAdmin', 'web')) {
            abort_unless(auth('web')->user()?->hasRole('SuperAdmin', 'web'), 403);
        }

        return view('users.edit', compact('user'));
    }

   
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }
}
