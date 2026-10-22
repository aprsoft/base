@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Crear Permiso" />

    <div class="space-y-6">
        <livewire:central.permission.create-permission />
    </div>
@endsection