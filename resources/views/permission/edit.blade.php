@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Editar Permiso" />

    <div class="space-y-6">
        <livewire:central.permission.edit-permission
            :permission-id="$permission->id"
        />
    </div>
@endsection