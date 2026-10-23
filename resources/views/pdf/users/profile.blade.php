@extends('pdf.layouts.base')

@section('title', 'Perfil del usuario')

@section('content')
    <p class="muted">Identificador de usuario: #{{ $user->id }}</p>

    <h2>Datos de la cuenta</h2>
    <table>
        <tbody>
            <tr><th>Nombre</th><td>{{ $user->name }}</td></tr>
            <tr><th>Correo electrónico</th><td>{{ $user->email ?? 'Sin información' }}</td></tr>
            <tr><th>Estado</th><td>{{ $user->trashed() ? 'Suspendido' : 'Activo' }}</td></tr>
            <tr><th>Correo verificado</th><td>{{ $user->email_verified_at ? 'Sí' : 'No' }}</td></tr>
            <tr><th>Fecha de registro</th><td>{{ $user->created_at?->format('d/m/Y H:i') ?? 'Sin información' }}</td></tr>
            <tr><th>Última actualización</th><td>{{ $user->updated_at?->format('d/m/Y H:i') ?? 'Sin información' }}</td></tr>
            @if ($user->trashed())
                <tr><th>Fecha de suspensión</th><td>{{ $user->deleted_at?->format('d/m/Y H:i') ?? 'Sin información' }}</td></tr>
            @endif
        </tbody>
    </table>

    <h2>Roles asignados</h2>
    <p>{{ $roles->isEmpty() ? 'Sin roles asignados' : $roles->pluck('name')->implode(', ') }}</p>
@endsection
