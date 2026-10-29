<?php

namespace App\Livewire\User;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Services\ErrorLog\ErrorLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Throwable;

class UserCreate extends Component
{
    public string $name = '';
    public string $email = '';
    public array $roleIds = [];
   

    // Obtiene todos los roles que existen en la BD
    private function assignableRoles(): Collection
    {
        $actor = auth('web')->user();
        abort_unless($actor, 403);

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->with('permissions')
            ->get();

        if ($actor->hasRole('SuperAdmin', 'web')) {
            return $roles;
        }

        // Una cuenta no puede conceder a terceros permisos que no posee.
        $allowed = $actor->getAllPermissions()
            ->where('guard_name', 'web')
            ->pluck('name')
            ->all();

        return $roles->filter(
            fn (Role $role) => $role->name !== 'SuperAdmin'
                && $role->permissions->every(
                    fn ($permission) => in_array($permission->name, $allowed, true)
                )
        )->values();
    }

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique(User::class, 'email'),
            ],

            'roleIds' => [
                'required',
                'array',
                'min:1',
            ],

            'roleIds.*' => [
                'integer',
                'distinct',
                Rule::in($this->assignableRoles()->modelKeys()),
            ],
        ];
    }

    protected array $messages = [
        'name.required' => 'Debes ingresar el nombre del usuario.',
        'name.max' => 'El nombre no puede superar los 255 caracteres.',

        'email.required' => 'Debes ingresar el correo electrónico.',
        'email.email' => 'El correo electrónico ingresado no es válido.',
        'email.max' => 'El correo electrónico no puede superar los 255 caracteres.',
        'email.unique' => 'El correo electrónico ingresado ya está registrado.',

        'roleIds.required' => 'Debes seleccionar al menos un rol.',
        'roleIds.array' => 'La selección de roles no es válida.',
        'roleIds.min' => 'Debes seleccionar al menos un rol.',

        'roleIds.*.integer' => 'Uno de los roles seleccionados no es válido.',
        'roleIds.*.in' => 'Uno de los roles seleccionados no está permitido.',
        'roleIds.*.distinct' => 'No puedes seleccionar un rol más de una vez.',
    ];

    public function confirmSave(): void
    {
        Gate::authorize('create', User::class);       

        $this->name = trim($this->name);
        $this->email = Str::lower(trim($this->email));
        $this->validate();

        $this->dispatch(
            'open-confirm-modal',
            id: 0,
            name: $this->name,
            confirmEvent: 'user-create-save-confirmed',
            title: 'Crear usuario',
            message: '¿Confirmas que deseas crear el usuario y asignarle los roles seleccionados?',
            warning: 'Se solicitará un enlace al correo indicado para establecer su contraseña.',
            confirmLabel: 'Sí, crear'
        );
    }

    public function confirmRemoveRole(int $roleId): void
    {        

        $role = $this->assignableRoles()->firstWhere('id', $roleId);
        if (! $role || ! in_array($roleId, array_map('intval', $this->roleIds), true)) {
            return;
        }

        $this->dispatch(
            'open-confirm-modal',
            id: $roleId,
            name: $role->name,
            confirmEvent: 'user-create-remove-role-confirmed',
            title: 'Quitar rol',
            message: '¿Confirmas que deseas quitar este rol de la selección?',
            warning: 'El usuario todavía no ha sido creado.',
            confirmLabel: 'Sí, quitar'
        );
    }

    #[\Livewire\Attributes\On('user-create-remove-role-confirmed')]
    public function removeRole(int $roleId): void
    {
      

        if (! $this->assignableRoles()->contains('id', $roleId)) {
            return;
        }

        $this->roleIds = array_values(array_filter(
            $this->roleIds,
            fn ($id) => (int) $id !== $roleId
        ));
    }

    #[\Livewire\Attributes\On('user-create-save-confirmed')]
    public function save(int $id, ErrorLogger $errorLogger)
    {
        Gate::authorize('create', User::class);      

        $this->name = trim($this->name);
        $this->email = Str::lower(trim($this->email));

        // Permitir que Livewire muestre los errores en el formulario.
        $validated = $this->validate();

        /*
        |--------------------------------------------------------------------------
        | Crear usuario y asignar roles
        |--------------------------------------------------------------------------
        */

        try {
            $user = DB::transaction(function () use ($validated) {

                $roles = $this->assignableRoles()
                    ->whereIn('id', $validated['roleIds']);

                if ($roles->count() !== count($validated['roleIds'])) {
                    throw ValidationException::withMessages([
                        'roleIds' => 'Los roles seleccionados ya no están disponibles.',
                    ]);
                }

                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],

                    // Contraseña temporal desconocida para el usuario.
                    // El modelo User aplica el cast "hashed".
                    'password' => Str::random(64),
                ]);

                $user->syncRoles($roles);

                return $user;
            });

        } catch (ValidationException $exception) {

            throw $exception;

        } catch (QueryException $exception) {

            $errorLogger->report($exception, [
                'operation' => 'user.create',
                'error_type' => 'database',
            ]);

            $this->addError(
                'form',
                'Ocurrió un error de base de datos al crear el usuario.'
            );

            return;

        } catch (Throwable $exception) {

            $errorLogger->report($exception, [
                'operation' => 'user.create',
                'error_type' => 'unexpected',
            ]);

            $this->addError(
                'form',
                'Ocurrió un error inesperado al crear el usuario.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Enviar enlace para establecer contraseña
        |--------------------------------------------------------------------------
        |
        | El usuario ya fue guardado correctamente.
        | El correo se solicita fuera de la transacción.
        |
        */

        try {

            $status = Password::sendResetLink([
                'email' => $user->email,
            ]);

        } catch (Throwable $exception) {

            $errorLogger->report($exception, [
                'operation' => 'user.create.password_link',
                'user_id' => $user->id,
                'error_type' => 'notification',
            ]);

            session()->flash(
                'warning',
                sprintf(
                    'El usuario "%s" fue creado correctamente, pero ocurrió un error al enviar el enlace para establecer su contraseña.',
                    $user->name
                )
            );

            return redirect()->route('users.index');
        }

        /*
        |--------------------------------------------------------------------------
        | Comprobar resultado del envío
        |--------------------------------------------------------------------------
        */

        if ($status !== Password::RESET_LINK_SENT) {

            Log::warning(
                'No se pudo procesar el envío del enlace de contraseña.',
                [
                    'user_id' => $user->id,
                    'status' => $status,
                ]
            );

            session()->flash(
                'warning',
                sprintf(
                    'El usuario "%s" fue creado correctamente, pero no se pudo solicitar el envío del enlace para establecer su contraseña.',
                    $user->name
                )
            );

            return redirect()->route('users.index');
        }

        /*
        |--------------------------------------------------------------------------
        | Creación exitosa
        |--------------------------------------------------------------------------
        */

        session()->flash(
            'success',
            sprintf(
                'El usuario "%s" fue creado correctamente y se solicitó el envío del enlace para establecer su contraseña.',
                $user->name
            )
        );

        return redirect()->route('users.index');
    }

    public function render(): View
    {
        Gate::authorize('create', User::class);        $roles = $this->assignableRoles();

        $selectedIds = array_map('intval', $this->roleIds);

        return view('livewire.user.user-create', [
            'roles' => $roles,

            'selectedRoles' => $roles->filter(
                fn (Role $role) => in_array(
                    (int) $role->id,
                    $selectedIds,
                    true
                )
            ),
        ]);
    }
}
