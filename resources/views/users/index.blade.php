@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Usuarios" />

    <div class="space-y-6">
        
        <livewire:user.user-table/> 
    
    </div>
@endsection

