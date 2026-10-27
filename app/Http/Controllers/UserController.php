<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', User::class);
        return view('users.index', ['title' => 'Usuarios']);
    }

    public function show(User $user)
    {
        Gate::authorize('view', $user);
        return view('users.show', ['title' => 'Mostrar usuario', 'user' => $user]);
    }

    public function create()
    {
        Gate::authorize('create', User::class);
        return view('users.create', ['title' => 'Crear Usuario']);
    }

    public function edit(User $user)
    {
        Gate::authorize('manage', $user);
        return view('users.edit', compact('user'));
    }
}