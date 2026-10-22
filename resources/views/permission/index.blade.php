@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Permisos" />

    <div>
        Permisos
    </div>

    <div class="space-y-6">
        <livewire:permission.permission-table />
    </div>
  
@endsection