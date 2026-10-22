@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Crear Rol" />

    <div class="space-y-6">
        <livewire:central.role.create-role />
    </div>
@endsection