<?php

use App\Http\Controllers\ErrorController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Models\ErrorLog;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('pdf/{type}/{record}', [PdfController::class, 'show'])
        ->whereNumber('record')->name('pdf.show');
    Route::get('pdf/{type}/{record}/download', [PdfController::class, 'download'])
        ->whereNumber('record')->name('pdf.download');

    Route::get('/dashboard', function () {
        return view('dashboard', ['title' => 'Dashboard']);
    })->name('dashboard');

    // Usuarios
    Route::get('users', [UserController::class, 'index'])
        ->middleware('can:viewAny,'.User::class)->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])
        ->middleware('can:create,'.User::class)->name('users.create');
    Route::get('users/{user}', [UserController::class, 'show'])
        ->withTrashed()->middleware('can:view,user')->name('users.show');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])
        ->withTrashed()->middleware('can:manage,user')->name('users.edit');

    // Roles
    Route::get('roles', [RoleController::class, 'index'])
        ->middleware('can:viewAny,'.Role::class)->name('roles.index');
    Route::get('roles/create', [RoleController::class, 'create'])
        ->middleware('can:create,'.Role::class)->name('roles.create');
    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])
        ->name('roles.edit');
    Route::get('roles/{role}', [RoleController::class, 'show'])
        ->name('roles.show');

    // Permisos
    Route::get('permissions', [PermissionController::class, 'index'])
        ->middleware('can:viewAny,'.Permission::class)->name('permissions.index');
    Route::get('permissions/create', [PermissionController::class, 'create'])
        ->middleware('can:create,'.Permission::class)->name('permissions.create');
    Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])
        ->name('permissions.edit');
    Route::get('permissions/{permission}', [PermissionController::class, 'show'])
        ->name('permissions.show');

    // Registro de errores
    Route::get('errors', [ErrorController::class, 'index'])
        ->middleware('can:viewAny,'.ErrorLog::class)->name('errors.index');
    Route::get('errors/{errorLog}', [ErrorController::class, 'show'])
        ->name('errors.show');
});

require __DIR__.'/auth.php';