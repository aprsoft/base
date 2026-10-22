@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Roles" />

     <div class="space-y-6">
        roles
    </div>

    <div class="space-y-6">
        <livewire:role.role-table/> 
    </div>
@endsection