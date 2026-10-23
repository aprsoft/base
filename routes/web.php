<?php

use App\Http\Controllers\ErrorController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    // Tipos de PDF permitidos exclusivamente por PdfRegistry.
    Route::get('pdf/{type}/{record}', [PdfController::class, 'show'])
        ->whereNumber('record')->name('pdf.show');
    Route::get('pdf/{type}/{record}/download', [PdfController::class, 'download'])
        ->whereNumber('record')->name('pdf.download');

    Route::get('/dashboard', function () {
        return view('dashboard', ['title' => 'Dashboard']);
    })->name('dashboard');

        /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */

        Route::get('users', [UserController::class, 'index'])
            ->middleware('can:viewAny,'.User::class)->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])
            ->middleware('can:create,'.User::class)->name('users.create');
        Route::get('users/{user}', [UserController::class, 'show'])
            ->withTrashed()->middleware('can:view,user')->name('users.show');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])
            ->withTrashed()->middleware('can:update,user')->name('users.edit');
        

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    */

    Route::get('roles', [RoleController::class, 'index'])
        ->name('roles.index');

    Route::get('roles/create', [RoleController::class, 'create'])
        ->name('roles.create');

    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])
        ->name('roles.edit');           
    
    
    Route::get('roles/{role}', [RoleController::class, 'show'])
        ->name('roles.show');        



    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */

    Route::get('permissions', [PermissionController::class, 'index'])
        ->name('permissions.index');

    Route::get('permissions/create', [PermissionController::class, 'create'])
        ->name('permissions.create');

    Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])
        ->name('permissions.edit');

    Route::get('permissions/{permission}', [PermissionController::class, 'show'])
        ->name('permissions.show');

    /*
    |--------------------------------------------------------------------------
    | Error_log
    |--------------------------------------------------------------------------
    */

        Route::get('errors', [ErrorController::class, 'index'])->name('errors.index');
        Route::get('errors/{errorLog}', [ErrorController::class, 'show'])->name('errors.show');

});

require __DIR__.'/auth.php';
