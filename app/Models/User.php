<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;
    use HasRoles;

    /**
     * Identificador de la cuenta principal del sistema.
     */
    public const PRIMARY_SUPER_ADMIN_KEY = 'primary_superadmin';

    /**
     * Atributos que admiten asignación masiva.
     *
     * system_account_key está excluido intencionalmente.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Atributos ocultos durante la serialización.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversión de atributos.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Determina si el usuario es el SuperAdmin principal.
     */
    public function isPrimarySuperAdmin(): bool
    {
        return $this->system_account_key
            === self::PRIMARY_SUPER_ADMIN_KEY;
    }

    /**
     * Obtiene las iniciales del usuario.
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(
                fn (string $name) =>
                    Str::of($name)->substr(0, 1)
            )
            ->implode('');
    }
}
