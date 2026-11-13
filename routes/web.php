<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\MeterController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Tenant\Admin\ContractController;
use App\Http\Controllers\Tenant\Admin\PaymentController;
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
Route::get('/', fn () => view('welcome'))->name('home');

/*
|--------------------------------------------------------------------------
| Rutas autenticadas
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    /*
    |----------------------------------------------------------------------
    | Usuarios - UserPolicy
    |----------------------------------------------------------------------
    */
    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->middleware('can:viewAny,'.User::class)->name('index');
        Route::get('create', 'create')->middleware('can:create,'.User::class)->name('create');
        Route::get('{user}', 'show')->withTrashed()->middleware('can:view,user')->name('show');
        // manage permite editar o acceder para restaurar.
        Route::get('{user}/edit', 'edit')->withTrashed()->middleware('can:manage,user')->name('edit');
    });

    /*
    |----------------------------------------------------------------------
    | Roles - RolePolicy
    |----------------------------------------------------------------------
    */
    Route::prefix('roles')->name('roles.')->controller(RoleController::class)->group(function () {
        Route::get('/', 'index')->middleware('can:viewAny,'.Role::class)->name('index');
        Route::get('create', 'create')->middleware('can:create,'.Role::class)->name('create');
        // Las rutas de detalle y edición utilizan permisos existentes.
        Route::get('{role}/edit', 'edit')->middleware('can:roles.edit')->name('edit');
        Route::get('{role}', 'show')->middleware('can:roles.show')->name('show');
    });

    /*
    |----------------------------------------------------------------------
    | Permisos - PermissionPolicy
    |----------------------------------------------------------------------
    */
    Route::prefix('permissions')->name('permissions.')->controller(PermissionController::class)->group(function () {
        Route::get('/', 'index')->middleware('can:viewAny,'.Permission::class)->name('index');
        Route::get('create', 'create')->middleware('can:create,'.Permission::class)->name('create');
        Route::get('{permission}/edit', 'edit')->middleware('can:permissions.edit')->name('edit');
        Route::get('{permission}', 'show')->middleware('can:permissions.show')->name('show');
    });

    /*
    |----------------------------------------------------------------------
    | Clientes
    |----------------------------------------------------------------------
    */
    Route::prefix('customers')->name('customers.')->controller(CustomerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{customer}', 'show')->name('show');
        Route::get('{customer}/edit', 'edit')->name('edit');
        Route::delete('{customer}', 'destroy')->name('destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Medidores
    |----------------------------------------------------------------------
    */
    Route::prefix('meters')->name('meters.')->controller(MeterController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{meter}', 'show')->name('show');
        Route::get('{meter}/edit', 'edit')->name('edit');
        Route::put('{meter}', 'update')->name('update');
        Route::delete('{meter}', 'destroy')->name('destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Contratos
    |----------------------------------------------------------------------
    */
    Route::prefix('contracts')->name('contracts.')->controller(ContractController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{contract}', 'show')->name('show');
        Route::get('{contract}/edit', 'edit')->name('edit');
        Route::put('{contract}', 'update')->name('update');
        Route::delete('{contract}', 'destroy')->name('destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Pagos
    |----------------------------------------------------------------------
    */
    Route::prefix('payments')->name('payments.')->controller(PaymentController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('index-create', 'indexCreate')->name('index.create');
        Route::get('create/customer/{customer}', 'create')->name('create.customer');
        Route::get('create/meter/{meter}', 'create')->name('create.meter');
        Route::post('/', 'store')->name('store');
        Route::get('{payment}', 'show')->name('show');
        Route::get('{payment}/edit', 'edit')->name('edit');
        Route::put('{payment}', 'update')->name('update');
        Route::delete('{payment}', 'destroy')->name('destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Documentos
    |----------------------------------------------------------------------
    */
    Route::prefix('documents')->name('documents.')->controller(DocumentController::class)->group(function () {
        // Específicas antes de las rutas con parámetros opcionales.
        // Se mantiene index() hasta confirmar que existe create() en el controlador.
        Route::get('customer/create', 'create')->name('customer.create');

        Route::get('customer/{customer?}', 'index')->name('customer.index');
        Route::get('supplier/{supplier?}', 'index')->name('supplier.index');
        Route::get('meter/{meter?}', 'index')->name('meter.index');
        Route::get('contract/{contract}', 'index')->name('contract.index');

        Route::get('payment/customer/{customer?}', 'index')->name('payment.customer.index');
        Route::get('payment/supplier/{supplier?}', 'index')->name('payment.supplier.index');
        Route::get('payment/meter/{meter?}', 'index')->name('payment.meter.index');
        Route::get('payment/contract/{contract}', 'index')->name('payment.contract.index');

        // Debe estar después de las rutas específicas.
        // {document} coincide con el argumento Document $document del controlador.
        Route::get('{document}', 'show')->name('show');
    });

    /*
    |----------------------------------------------------------------------
    | Documentos PDF
    |----------------------------------------------------------------------
    */
    Route::prefix('pdf')->name('pdf.')->controller(PdfController::class)->group(function () {
        Route::get('{type}/{record}', 'show')->whereNumber('record')->name('show');
        Route::get('{type}/{record}/download', 'download')->whereNumber('record')->name('download');
    });

    /*
    |----------------------------------------------------------------------
    | Registro de errores - ErrorLogPolicy
    |----------------------------------------------------------------------
    */
    Route::prefix('errors')->name('errors.')->controller(ErrorController::class)->group(function () {
        Route::get('/', 'index')->middleware('can:viewAny,'.ErrorLog::class)->name('index');
        Route::get('{id}', 'show')->middleware('can:errors.show')->name('show');
    });
});

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';