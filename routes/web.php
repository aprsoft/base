
<?php

use App\Http\Controllers\ErrorController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Models\ErrorLog;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| Página principal
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Rutas autenticadas
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard', ['title' => 'Dashboard']);
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Usuarios - UserPolicy
    |--------------------------------------------------------------------------
    */

    Route::get('users', [UserController::class, 'index'])
        ->middleware('can:viewAny,'.User::class)
        ->name('users.index');

    Route::get('users/create', [UserController::class, 'create'])
        ->middleware('can:create,'.User::class)
        ->name('users.create');

    Route::get('users/{user}', [UserController::class, 'show'])
        ->withTrashed()
        ->middleware('can:view,user')
        ->name('users.show');

    // manage permite editar o acceder para restaurar.
    Route::get('users/{user}/edit', [UserController::class, 'edit'])
        ->withTrashed()
        ->middleware('can:manage,user')
        ->name('users.edit');

    /*
    |--------------------------------------------------------------------------
    | Roles - RolePolicy
    |--------------------------------------------------------------------------
    */

    Route::get('roles', [RoleController::class, 'index'])
        ->middleware('can:viewAny,'.Role::class)
        ->name('roles.index');

    Route::get('roles/create', [RoleController::class, 'create'])
        ->middleware('can:create,'.Role::class)
        ->name('roles.create');

    // El controlador verifica la Policy sobre el rol consultado.
    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])
        ->middleware('can:roles.edit')
        ->name('roles.edit');

    Route::get('roles/{role}', [RoleController::class, 'show'])
        ->middleware('can:roles.show')
        ->name('roles.show');

    /*
    |--------------------------------------------------------------------------
    | Permisos - PermissionPolicy
    |--------------------------------------------------------------------------
    */

    Route::get('permissions', [PermissionController::class, 'index'])
        ->middleware('can:viewAny,'.Permission::class)
        ->name('permissions.index');

    Route::get('permissions/create', [PermissionController::class, 'create'])
        ->middleware('can:create,'.Permission::class)
        ->name('permissions.create');

    // El controlador verifica la Policy sobre el permiso consultado.
    Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])
        ->middleware('can:permissions.edit')
        ->name('permissions.edit');

    Route::get('permissions/{permission}', [PermissionController::class, 'show'])
        ->middleware('can:permissions.show')
        ->name('permissions.show');

    /*
    |--------------------------------------------------------------------------
    | Documentos PDF
    |--------------------------------------------------------------------------
    */

    Route::get('pdf/{type}/{record}', [PdfController::class, 'show'])
        ->whereNumber('record')
        ->name('pdf.show');

    Route::get('pdf/{type}/{record}/download', [PdfController::class, 'download'])
        ->whereNumber('record')
        ->name('pdf.download');

    /*
    |--------------------------------------------------------------------------
    | Registro de errores - ErrorLogPolicy
    |--------------------------------------------------------------------------
    */

    Route::get('errors', [ErrorController::class, 'index'])
        ->middleware('can:viewAny,'.ErrorLog::class)
        ->name('errors.index');

    // El controlador verifica la Policy sobre el registro.
    Route::get('errors/{id}', [ErrorController::class, 'show'])
        ->middleware('can:errors.show')
        ->name('errors.show');

});

require __DIR__.'/auth.php';
