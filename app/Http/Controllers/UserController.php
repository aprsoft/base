<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;

class UserController extends Controller
{
  
    public function index()
    {        
        abort_unless(auth('web')->user()?->can('users.index'), 403);
        abort_if((int) $user->id === 1, 403);
        abort_if($user->trashed(), 404);

        return view('users.index', ['title'=> 'Usuarios']);
    }

    public function show(User $user)
    {
        abort_unless(auth('web')->user()?->can('users.show'), 403);
        abort_if((int) $user->id === 1, 403);
        abort_if($user->trashed(), 404);
        
        return view('users.show',['title'=>'Mostrar Usuario','user'=> $user]);
    }

    public function create()
    {
        abort_unless(auth('web')->user()?->can('users.create'), 403);
        abort_if((int) $user->id === 1, 403);
        abort_if($user->trashed(), 404);

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

   
}
