@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Editar Rol" />

    <div class="space-y-6">
        <livewire:role.edit-role
            :role-id="$role->id"
        />
    </div>
@endsection